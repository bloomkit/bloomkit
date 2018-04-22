<?php

namespace Bloomkit\Core\Routing;

class RouteCollection
{
    /**
     * @var array
     */
    private $routes = [];

    /**
     * Returns the number of Routes in this collection.
     *
     * @return int
     */
    public function getCount()
    {
        return count($this->routes);
    }

    /**
     * Returns the routes.
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    public function add($name, Route $route)
    {
        unset($this->routes[$name]);

        $this->routes[$name] = $route;
    }

    public function getRoute($name)
    {
        return isset($this->routes[$name]) ? $this->routes[$name] : null;
    }

    public function remove($name)
    {
        foreach ((array) $name as $n) {
            unset($this->routes[$n]);
        }
    }

    public function addCollection(RouteCollection $collection)
    {
        // we need to remove all routes with the same names first because just replacing them
        // would not place the new route at the end of the merged array
        foreach ($collection->getRoutes() as $name => $route) {
            // unset($this->routes[$name]);
            $this->routes[$name] = $route;
        }
    }

    public function addPrefix($prefix, array $defaults = array(), array $requirements = array())
    {
        $prefix = trim(trim($prefix), '/');

        if ('' === $prefix) {
            return;
        }

        foreach ($this->routes as $route) {
            $route->setPath('/'.$prefix.$route->getPath());
            $route->addDefaults($defaults);
            $route->addRequirements($requirements);
        }
    }

    public function setHost($pattern, array $defaults = array(), array $requirements = array())
    {
        foreach ($this->routes as $route) {
            $route->setHost($pattern);
            $route->addDefaults($defaults);
            $route->addRequirements($requirements);
        }
    }

    public function addDefaults(array $defaults)
    {
        if ($defaults) {
            foreach ($this->routes as $route) {
                $route->addDefaults($defaults);
            }
        }
    }

    public function addRequirements(array $requirements)
    {
        if ($requirements) {
            foreach ($this->routes as $route) {
                $route->addRequirements($requirements);
            }
        }
    }

    public function addOptions(array $options)
    {
        if ($options) {
            foreach ($this->routes as $route) {
                $route->addOptions($options);
            }
        }
    }

    public function setSchemes($schemes)
    {
        foreach ($this->routes as $route) {
            $route->setSchemes($schemes);
        }
    }

    public function setMethods($methods)
    {
        foreach ($this->routes as $route) {
            $route->setMethods($methods);
        }
    }

    public function getResources()
    {
        return array_unique($this->resources);
    }

    public function addResource(RessourceInterface $resource)
    {
        $this->resources[] = $resource;
    }
}
