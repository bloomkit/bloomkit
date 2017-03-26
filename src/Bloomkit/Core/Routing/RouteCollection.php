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
     * Returns the routes
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }
    
}
