<?php

namespace Bloomkit\Core\Http;

use Bloomkit\Core\Application\Application;
use Bloomkit\Core\Module\ModuleInterface;
use Bloomkit\Core\Routing\RouteCollection;
use Bloomkit\Core\Routing\Exceptions\RessourceNotFoundException;
use Bloomkit\Core\Http\Exceptions\HttpNotFoundException;
use Bloomkit\Core\Security\Exceptions\AuthConfigException;
use Bloomkit\Core\Security\FirewallListener;
use Bloomkit\Core\Security\Firewall;
use Bloomkit\Core\Http\Session\SessionListener;
use Bloomkit\Core\Http\Session\Session;
use Bloomkit\Core\Security\Exceptions\AccessDeniedException;

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
        $this->registerFactory('routeMatcher', 'Bloomkit\Core\Routing\RouteMatcher', true);

        $this->registerFactory('session', function (Application $application) {
            $session = new Session();
            $session->start();

            return $session;
        }, true);

        $this->registerFactory('firewall', function (Application $application) {
            //$listener = new LoginFormAuthenticationListener();
            $firewall = new Firewall($this->getSecurityContext(), $this->getLogger());
            //$firewall->addListener($listener);
            return $firewall;
        }, true);

        $this->setAlias('Bloomkit\Core\Routing\RouteCollection', 'routes');

        $this->bind('Psr\Log\LoggerInterface', 'Bloomkit\Core\Application\DummyLogger');

        $this->getEventManager()->addSubscriber(new SessionListener($this));
        $this->getEventManager()->addSubscriber(new FirewallListener($this));
    }

    /**
     * Returns the route matcher.
     *
     * @return \Bloomkit\Core\Routing\RouteMatcher
     */
    public function getRouteMatcher()
    {
        return $this['routeMatcher'];
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

            foreach ($this->modules as $module) {
                $module->onModulesLoaded();
            }

            if ($event->hasResponse()) {
                $this->getEventManager()->triggerEvent(HttpEvents::RESPONSE, $event);
                $this->getEventManager()->triggerEvent(HttpEvents::FINISH_REQUEST, $event);

                return $event->getResponse();
            }

            try {
                $matcher = $this->getRouteMatcher();
                $parameters = $matcher->match($request->getPathUrl(), $request->getHttpMethod());

                // Authentication
                if (isset($parameters['_auth'])) {
                    $auth = $parameters['_auth'];

                    if (isset($auth['options'])) {
                        $options = $auth['options'];
                    } else {
                        $options = [];
                    }

                    if ((isset($auth['authEntryPoint'])) && (class_exists($auth['authEntryPoint']))) {
                        $this->firewall->setAuthEntryPoint(new $auth['authEntryPoint']());
                    }

                    if (isset($auth['authenticator']) == false) {
                        throw new AuthConfigException(sprintf('"authenticator" parameter is missing in route-config for "%s"',
                            $request->getPathUrl()));
                    }

                    if (isset($auth['userProvider']) == false) {
                        throw new AuthConfigException(sprintf('"userProvider" parameter is missing in route-config for "%s"',
                            $request->getPathUrl()));
                    }

                    if (!class_exists($auth['authenticator'])) {
                        throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $auth['authenticator']));
                    }
                    if (!class_exists($auth['userProvider'])) {
                        throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $auth['userProvider']));
                    }

                    if (is_subclass_of($auth['userProvider'], 'Bloomkit\Core\Security\User\EntityUserProvider')) {
                        $userProvider = new $auth['userProvider']($this);
                    } else {
                        $userProvider = new $auth['userProvider']();
                    }

                    $authenticator = new $auth['authenticator']($userProvider);

                    $token = $this['securityContext']->getToken();

                    if ((is_null($token)) || ($authenticator->supportsToken($token) == false)) {
                        $token = $authenticator->createToken($request);
                    }

                    if ($authenticator->supportsToken($token) == false) {
                        throw new \Exception('Token is not supported');
                    }
                    $token = $authenticator->authenticateToken($token, $userProvider, $options);
                    $this->getSecurityContext()->setToken($token);
                }

                // Authorization
                if (isset($parameters['_perm'])) {
                    $action = $parameters['_perm'];

                    $user = $token->getUser();
                    if (is_null($user)) {
                        throw new AccessDeniedException('No user found');
                    }
                    $policy = $user->getPolicy();

                    if (is_null($policy)) {
                        throw new AccessDeniedException('No policy found');
                    }
                    if (true !== $policy->isAllowed($action, '*')) {
                        throw new AccessDeniedException('Permission denied');
                    }
                }

                $request->getAttributes()->addItems($parameters);
                $controllerName = $parameters['_controller'];

                $this->getEventManager()->triggerEvent(HttpEvents::CONTROLLER, $event);

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

                $attributes = $request->getAttributes()->getItems();
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

                $controller = $this->createObject($class);
                $controller->setRequest($request);

                $response = call_user_func_array([$controller, $method], $arguments);

                $this->getEventManager()->triggerEvent(HttpEvents::VIEW, $event);
                $event->setResponse($response);
                $this->getEventManager()->triggerEvent(HttpEvents::RESPONSE, $event);
                $this->getEventManager()->triggerEvent(HttpEvents::FINISH_REQUEST, $event);

                return $response;
            } catch (RessourceNotFoundException $e) {
                $message = sprintf('No route found for "%s %s"', $request->getHttpMethod(), $request->getPathUrl());
                throw new HttpNotFoundException($message, 404);
            }
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
    public function run($sendResponse = true)
    {
        $request = HttpRequest::processRequest();
        $response = $this->process($request);

        $event = new HttpEvent($request);
        $event->setResponse($response);
        $this->getEventManager()->triggerEvent(HttpEvents::RESPONSE, $event);
        $response = $event->getResponse();
        if ($sendResponse) {
            $response->send();
        }
        $this->getEventManager()->triggerEvent(HttpEvents::TERMINATE, $event);

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
