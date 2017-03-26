<?php
namespace Bloomkit\Core\Routing;

use Bloomkit\Core\Routing\Exceptions\RessourceNotFoundException;

class UrlMatcher
{	
	protected $routes;
	
	protected $allow = [];

	public function __construct(RouteCollection $routes)
	{
		$this->routes  = $routes;
	}	
	
	public function match($pathinfo, $method = null)
	{
	    $this->allow = [];
	
		if ($ret = $this->matchCollection(rawurldecode($pathinfo), $this->routes, $method))
			return $ret;
	
		if (count($this->allow) > 0)
			throw new MethodNotAllowedException(array_unique(array_map('strtoupper', $this->allow)));
		else
			throw new RessourceNotFoundException();
	}
	
	protected function matchCollection($pathinfo, RouteCollection $routes, $method = null)
	{
	    foreach ($routes->getRoutes() as $name => $route) {

	    }
    }
	
}