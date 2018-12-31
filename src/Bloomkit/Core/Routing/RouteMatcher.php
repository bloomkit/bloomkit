<?php

namespace Bloomkit\Core\Routing;

use Bloomkit\Core\Routing\Exceptions\RessourceNotFoundException;

/**
 * Class for finding matching routes for an url.
 */
class RouteMatcher
{
    /**
     * @var RouteCollection
     */
    protected $routes;

    /**
     * Constructor.
     *
     * @param RouteCollection $routes The routes to match
     */
    public function __construct(RouteCollection $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Find a matching route for a path (+method +host).
     *
     * @param string      $path   The url-path to find a matching route for
     * @param string|null $method An optional HTTP-method requirement
     * @param string|null $host   An optional host requirement
     */
    public function match($path, $method = null, $host = null)
    {
        if ($match = $this->matchCollection(rawurldecode($path), $this->routes, $method, $host)) {
            return $match;
        }

        throw new RessourceNotFoundException();
    }

    /**
     * Search the route collection for the requested path (+method + host).
     *
     * @param string          $path   The url-path to find a matching route for
     * @param RouteCollection $routes The routes to check for a match
     * @param string|null     $method An optional HTTP-method requirement
     * @param string|null     $host   An optional host requirement
     */
    protected function matchCollection($path, RouteCollection $routes, $method = null, $host = null)
    {
        foreach ($routes->getRoutes() as $name => $route) {
            $compiledRoute = $route->compile();

            $staticPrefix = $compiledRoute->getStaticPrefix();

            //quickcheck if route may match
            if (('' !== $staticPrefix) && (0 !== strpos($path, $staticPrefix))) {
                continue;
            }

            //if it does, check the regex
            if (!preg_match($compiledRoute->getRegex(), $path, $matches)) {
                continue;
            }

            //check if host matches
            $hostMatches = [];
            if (isset($host)) {
                $hostRegex = $compiledRoute->getHostRegex();
                if ($hostRegex && !preg_match($hostRegex, $host, $hostMatches)) {
                    continue;
                }
            }

            //check if method matches
            if (isset($method)) {
                $supportedMethods = $route->getMethods();
                // HEAD and GET are equivalent as per RFC
                if ('HEAD' == $method) {
                    $method = 'GET';
                }

                if (!in_array($method, $supportedMethods)) {
                    continue;
                }
            }

            //return the data of the matching route
            $routeAttributes = $route->getAttributes();
            $routeAttributes = array_merge($matches, $routeAttributes);

            $routeAttributes['_route'] = $name;

            return $routeAttributes;
        }
    }
}
