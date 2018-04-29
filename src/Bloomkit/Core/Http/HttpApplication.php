<?php

namespace Bloomkit\Core\Http;

use Bloomkit\Core\Application\Application;
use Bloomkit\Core\Module\ModuleInterface;
use Bloomkit\Core\Routing\RouteCollection;

class HttpApplication extends Application
{
    /**
     * Constuctor.
     *
     * {@inheritdoc}
     */
    public function __construct($appName = 'UNKNOWN', $appVersion = '0.0', $basePath = null, array $config = [])
    {
        parent::__construct($appName, $appVersion, $basePath, $config);

        $this->registerFactory('routes', 'Bloomkit\Core\Routing\RouteCollection', true);
        $this->registerFactory('route_matcher', 'Bloomkit\Core\Routing\RouteMatcher', true);

        $this->setAlias('Bloomkit\Core\Routing\RouteCollection', 'routes');

        $this->bind('Psr\Log\LoggerInterface', 'Bloomkit\Core\Application\DummyLogger');
    }

    /**
     * Returns the route matcher.
     *
     * @return \Bloomkit\Core\Routing\RouteMatcher
     */
    public function getRouteMatcher()
    {
        return $this['route_matcher'];
    }

    /**
     * Processing the request.
     *
     * @param HttpRequest $request The request to process
     *
     * @return HttpResponse The response to the request
     */
    private function process(HttpRequest $request)
    {
        try {
            $event = new HttpEvent($request);
            $this->getEventManager()->triggerEvent(HttpEvents::REQUEST, $event);

            if ($event->hasResponse()) {
                $this['eventManager']->triggerEvent(HttpEvents::RESPONSE, $event);

                return $event->getResponse();
            }

            $matcher = $this->getRouteMatcher();
            $parameters = $matcher->match($request->getPathUrl(), $request->getHttpMethod());

            $request->getAttributes()->addItems($parameters);
            $controllerName = $parameters['_controller'];

            $this['eventManager']->triggerEvent(HttpEvents::CONTROLLER, $event);

            if (false === strpos($controllerName, '::')) {
                throw new \InvalidArgumentException(sprintf('Unable to find controller "%s".', $controllerName));
            }

            $controllerInfo = list($class, $method) = explode('::', $controllerName, 2);

            if (!class_exists($class)) {
                throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
            }

            if (is_array($controllerInfo)) {
                $r = new \ReflectionMethod($controllerInfo[0], $controllerInfo[1]);
            }

            $params = $r->getParameters();

            $attributes = $request->attributes->getItems();
            $arguments = array();

            foreach ($params as $param) {
                if (array_key_exists($param->name, $attributes)) {
                    $arguments[] = $attributes[$param->name];
                } elseif ($param->getClass() && $param->getClass()->isInstance($request)) {
                    $arguments[] = $request;
                } elseif ($param->isDefaultValueAvailable()) {
                    $arguments[] = $param->getDefaultValue();
                } else {
                    if (is_array($controller)) {
                        $repr = sprintf('%s::%s()', $controller[0], $controller[1]);
                    } elseif (is_object($controller)) {
                        $repr = get_class($controller);
                    } else {
                        $repr = $controller;
                    }
                    throw new \RuntimeException(sprintf('Controller "%s" requires that you provide a value for the "$%s" argument (because there is no default value or because there is a non optional argument after this one).', $repr, $param->name));
                }
            }

            $controller = new $class($this);
            $controller->setRequest($request);

            $tracer->start('App::CallController');
            $response = call_user_func_array(array(
                    $controller,
                    $method,
                ), $arguments);
            $tracer->stop('App::CallController');

            $this['eventManager']->triggerEvent(HttpEvents::VIEW, $event);
            $event->setResponse($response);
            $this['eventManager']->triggerEvent(HttpEvents::RESPONSE, $event);
            $this['eventManager']->triggerEvent(HttpEvents::FINISH_REQUEST, $event);

            return $response;
        } catch (\Exception $e) {
            return $this->handleException($e, $request);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function registerModule(ModuleInterface $module)
    {
        parent::registerModule($module);
        $routes = $module->getRoutes();

        if (($routes instanceof RouteCollection) && ($routes->getCount() > 0)) {
            $this['routes']->addCollection($routes);
        }
    }

    /**
     * Start the application.
     */
    public function run()
    {
        $request = HttpRequest::processRequest();
        $response = $this->process($request);

        return $response;
    }

    /**
     * Handles an exception by trying to convert it to a Response.
     *
     * @param \Exception  $e
     * @param HttpRequest $request
     *
     * @return HttpResponse
     *
     * @throws \Exception
     */
    private function handleException(\Exception $e, HttpRequest $request)
    {
        $event = new HttpExceptionEvent($request, $e);
        $this['eventManager']->triggerEvent(HttpEvents::EXCEPTION, $event);

        // a listener might have replaced the exception
        $e = $event->getException();

        if (!$event->hasResponse()) {
            throw $e;
        }

        return $event->getResponse();
    }
}
