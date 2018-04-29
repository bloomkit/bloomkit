<?php

namespace Bloomkit\Core\Http;

use Bloomkit\Core\Application\Application;
use Bloomkit\Core\Module\ModuleInterface;

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
     * {@inheritdoc}
     */
    public function registerModule(ModuleInterface $module)
    {
        parent::registerModule($module);
        $routes = $module->getRoutes();
    
        if (($routes instanceof RouteCollection) && ($routes->getCount() > 0)) {
            $this['routes']->addCollection($routes);
        };
    }
    
    /**
     * Start the application.
     */
    public function run()
    {
        $request = HttpRequest::processRequest();
        $response = $this->handle($request);

        return $response;
    }

    private function handle(HttpRequest $request)
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
        } catch (\Exception $e) {
            return $this->handleException($e, $request);
        }
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
