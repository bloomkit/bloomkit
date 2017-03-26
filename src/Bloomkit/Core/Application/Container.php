<?php
namespace Bloomkit\Core\Application;

use Bloomkit\Core\Application\Exception\DiInstantiationException;

class Container implements \ArrayAccess
{
    /**
     * @var array
     */	
    protected $keys = [];

    /**
     * @var array
     */    
    protected $values = [];

    /**
     * @var array
     */    
    protected $instances = [];

    /**
     * @var array
     */    
    protected $factories = [];

    /**
     * @var array
     */    
    protected $bindings = [];

    /**
     * @var array
     */    
    protected $aliases = [];

    /**
     * @var array
     */    
    protected $rules = [];

    /**
     * @var array
     */    
    protected $buildStack = [];

    /**
     * Returns a container element (Mapper for array-access)
     *
     * @param string $key            
     */
    public function __get($key)
    {
        return $this[$key];
    }

    /**
     * Set a container element (Mapper for array-access)
     *
     * @param string $key            
     * @param mixed $value            
     */
    public function __set($key, $value)
    {
        $this[$key] = $value;
    }

    /**
     * Add something to the resolving-rules array
     *
     * @param string $what            
     * @param string $needs            
     * @param string $needs            
     */
    public function addRule($what, $needs, $give)
    {
        $what = $this->normalize($what);
        $needs = $this->normalize($needs);
        $this->rules[$what][$needs] = $give;
    }

    /**
     * Removes "\\" from the beginning of a string
     * 
     * @return string
     */
    protected function normalize($value)
    {
        if (! is_string($value))
            return $value;
        return ltrim($value, '\\');
    }

    /**
     * Set an alias for a value
     *
     * @param string $value            
     * @param string $alias            
     */
    public function setAlias($value, $alias)
    {
        $this->aliases[$alias] = $this->normalize($value);
    }
    
    /**
     * Add a binding (eg register a closure for an interface)
     *
     * @param string $abstract            
     * @param string $concrete            
     * @param boolean $shared            
     */
    public function bind($abstract, $concrete, $shared = false)
    {
        $abstract = $this->normalize($abstract);
        $concrete = $this->normalize($concrete);
        
        unset($this->instances[$abstract]);
        
        if (! $concrete instanceof \Closure)
            $concrete = $this->getClosure($concrete);
        
        $this->bindings[$abstract] = compact('concrete', 'shared');
    }
    
    /**
     * Returns the alias for a value
     *
     * @param string $value
     * 
     * @return string 
     */
    public function getAlias($value)
    {
        $alias = array_search($value, $this->aliases);
        if($alias==FALSE)
            return $value;
        return $alias;
        if (isset($this->aliases[$value]))
            return ($this->aliases[$value]);
        else
            return $value;
    }
    
    /**
     * Try to create an object from an abstract or alias
     *
     * @param string $abstract
     * @param array $parameters       
     *
     * @return object
     */
    public function make($abstract, array $parameters = [])
    {
        $abstract = $this->normalize($abstract);
        $alias = $this->getAlias($abstract);
        
        if ($alias!==$abstract)
        {        
            if (isset($this->values[$alias]))
                return $this->values[$alias];
        
            if (isset($this->factories[$alias]))
                return $this->resolveFactory($alias);
        }
        
        $concrete = $this->resolveAbstract($abstract);
        
        $this->buildStack[] = $abstract;
        
        if ($concrete instanceof \Closure)
            return $concrete($this);
        
        $object = $this->createObject($concrete, $parameters);
        
        return $object;
    }

    /**
     * Get the required parameters for a callback. If an object is required, try to create by calling "make"
     * Otherwise the default parameter ist used - if set. 
     *
     * @param string|callable $callback            
     * @param array $parameters        
     *     
     * @return array
     */
    protected function getCallbackParameters($callback, array $parameters = [])
    {
        $reflectionInfo = $this->getReflectionInfo($callback);
        $paramInfos = $reflectionInfo->getParameters();
        
        $resolvedParameters = array();
        
        foreach ($paramInfos as $parameter) {
            if ($parameter->getClass()) {
                $resolvedParameters[] = $this->make($parameter->getClass()->name);
            } elseif ($parameter->isDefaultValueAvailable()) {
                $resolvedParameters[] = $parameter->getDefaultValue();
            }
        }
        return array_merge($resolvedParameters, $parameters);
    }

    /**
     * Returns reflection information for the given callback.
     *
     * @param string|callable $callback 
     *            
     * @return \ReflectionFunctionAbstract
     */
    protected function getReflectionInfo($callback)
    {
        if (is_string($callback) && strpos($callback, '::') !== false)
            $callback = explode('::', $callback);
        
        if (is_array($callback))
            return new \ReflectionMethod($callback[0], $callback[1]);
        
        return new \ReflectionFunction($callback);
    }

    /**
     * ArrayAccess function: Check if container-element exists
     *
     * @param string $key            
     */
    public function offsetExists($key)
    {
        return isset($this->keys[$key]);
    }

    /**
     * ArrayAccess function: Set a container element - Mapper for "register" function
     *
     * @param string $key            
     * @param mixed $value            
     */
    public function offsetSet($key, $value)
    {
        $this->register($key, $value);
    }

    /**
     * ArrayAccess function: Unset a container element
     *
     * @param string $key            
     */
    public function offsetUnset($key)
    {
        unset($this->keys[$key]);
        unset($this->factories[$key]);
        unset($this->values[$key]);
    }

    /**
     * ArrayAccess function: Returns a container element - Mapper for "resolve" function
     *
     * @param string $key            
     */
    public function offsetGet($key)
    {
        return $this->resolve($key);
    }
    
    /**
     * Call the given callback with dependency injection support
     *
     * @param callable|string $callback
     * @param array $parameters
     * 
     * @return mixed
     */
    protected function call($callback, array $parameters = [])
    {
        $parameters = $this->getCallbackParameters($callback, $parameters);
        return call_user_func_array($callback, $parameters);
    }
    
    /**
     * Create an object with dependency injection support
     *
     * @param string $class
     * @param array $parameters
     * 
     * @return object
     * 
     * @throws DiInstantiationException If target is not instantiable  
     */
    protected function createObject($class, array $parameters = [])
    {
        $r = new \ReflectionClass($class);
        
        if (method_exists($class, '__construct')) {
            $callback = array($class,'__construct');
            $parameters = $this->getCallbackParameters($callback, $parameters);
        }        
        
        if (! $r->isInstantiable()) {
            if (! empty($this->buildStack)) {
                $previous = implode(', ', $this->buildStack);
                $message = "Cannot create [$class] while building [$previous].";
            } else {
                $message = "Cannot create [$class] ";
            }
            throw new DiInstantiationException($message);
        }
        
        return $r->newInstanceArgs($parameters);
    }

    /**
     * Creates a closure for a classname
     *
     * @param string $class   
     * 
     * @return callable
     */
    protected function getClosure($class)
    {
        return function ($container, $parameters = []) use ($class) {
            return $container->createObject($class);
        };
    }
    
    
    /**
     * Register something with a key (value, factory, etc)
     *
     * @param string $key
     * @param mixed $value
     */
    public function register($key, $value)
    {
        $this->keys[$key] = true;
        if ($value instanceof \Closure)
            $this->registerFactory($key, $value, true);
        else
            $this->values[$key] = $value;
    }
    
    /**
     * Register a factory with a key - if "shared" = true register as singleton 
     *
     * @param string $key
     * @param mixed $factory
     * @param boolean $shared
     */
    public function registerFactory($key, $factory, $shared = false)
    {
        if (! $factory instanceof \Closure)
            $factory = $this->getClosure($factory);
        
        $this->keys[$key] = true;
        $this->factories[$key] = compact('factory', 'shared');
    }
    
    
    /**
     * Check for rules or bindings for an abstract and resolve it if available
     *
     * @param string $abstract            
     * 
     * @return mixed
     */
    protected function resolveAbstract($abstract)
    {
        $lastBuild = end($this->buildStack);
        if (isset($this->rules[$lastBuild][$abstract]))
            return $this->rules[$lastBuild][$abstract];
        
        if (isset($this->bindings[$abstract]))
            return $this->bindings[$abstract]['concrete'];
        
        return $abstract;
    }

    /**
     * Returns object for factory. Returns the same object, if factory is registered with "shared"=true
     *
     * @param string $key
     *
     * @return mixed Returns object or value if exist. Creates object if key is assigned to a factory.
     *
     * @throws \InvalidArgumentException If there ist nothing registered for the key
     */
    protected function resolveFactory($key)
    {
        if (! isset($this->factories[$key]))
            throw new \InvalidArgumentException(sprintf('Factory "%s" not found.', $key));
        if ($this->factories[$key]['shared']) {
            if (isset($this->instances[$key]))
                return $this->instances[$key];
            $this->instances[$key] = $this->factories[$key]['factory']($this);
            return $this->instances[$key];
        }
        return $this->factories[$key]['factory']($this);
    }

    /**
     * Returns an element bound to the container by its key
     *
     * @param string $key            
     *
     * @return mixed Returns object or value if exist. Creates object if key is assigned to a factory.
     *        
     * @throws \InvalidArgumentException If there ist nothing registered for the key
     */
    protected function resolve($key)
    {
        if (! isset($this->keys[$key]))
            throw new \InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $key));
        
        if (isset($this->values[$key]))
            return $this->values[$key];
        
        if (isset($this->factories[$key]))
            return $this->resolveFactory($key);
    }
}   