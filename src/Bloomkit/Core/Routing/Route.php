<?php

namespace Bloomkit\Core\Routing;

use Doctrine\Common\Annotations\Annotation\Attribute;

class Route
{
    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var CompiledRoute
     */
    private $compiled;

    /**
     * @var string
     */
    private $host = '';

    /**
     * @var array
     */
    private $methods = [];

    /**
     * @var string
     */
    private $path = '/';

    /**
     * @var array
     */
    private $requirements = [];

    /**
     * @var array
     */
    private $schemes = [];

    /**
     * Constructor.
     *
     * @param string       $path         The path pattern to match
     * @param array        $attributes   An array of data - passed to the controller if a route matches
     * @param string|array $methods      The supported HTTP-methods
     * @param array        $requirements An array of requirements for parameters (regexes)
     * @param string       $host         A host pattern to match
     * @param string|array $schemes      A required URI scheme or an array of restricted schemes     
     */
    public function __construct($path, array $attributes = [], $methods = [], array $requirements = [], $host = '', $schemes = [])
    {
        $this->setPath($path);
        $this->setAttributes($attributes);
        $this->setRequirements($requirements);
        $this->setHost($host);

        // The conditions make sure that an initial empty $schemes/$methods does not override the corresponding requirement.
        // They can be removed when the BC layer is removed.
        if ($schemes) {
            $this->setSchemes($schemes);
        }

        if ($methods) {
            $this->setMethods($methods);
        }
    }

    /**
     * Adds a list of attributes to the route.
     *
     * @param array $attributes The attributes to add
     */
    public function addAttributes(array $attributes)
    {
        foreach ($attributes as $name => $value) {
            $this->attributes[$name] = $value;
        }

        $this->compiled = null;
    }

    /**
     * Adds a list of param-requirements to the route.
     *
     * @param array $requirements The route param requirements to add
     */
    public function addRequirements(array $requirements)
    {
        foreach ($requirements as $key => $regex) {
            $this->requirements[$key] = $this->sanitizeRequirement($key, $regex);
        }
        $this->compiled = null;
    }

    /**
     * Compile the route (if not already done) and return it.
     *
     * @return CompiledRoute The compiled route
     */
    public function compile()
    {
        if (!is_null($this->compiled)) {
            return $this->compiled;
        }

        $this->compiled = RouteCompiler::compile($this);

        return $this->compiled;
    }

    /**
     * Deserialize a route from a string.
     *
     * @param string $data The serialized route
     */
    public function deserialize($data)
    {
        $data = unserialize($data);
        $this->path = $data['path'];
        $this->host = $data['host'];
        $this->attributes = $data['attributes'];
        $this->requirements = $data['requirements'];
        $this->schemes = $data['schemes'];
        $this->methods = $data['methods'];
    }

    /**
     * Return a route attribute by its name.
     *
     * @returns mixed|null The route attribute or null if not found
     */
    public function getAttribute($name)
    {
        if (isset($this->attribute[$name])) {
            return $this->attribute[$name];
        }
    }

    /**
     * Return the route attributes (controller, etc).
     *
     * @returns array The route attributes
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Return the required host for this route.
     *
     * @returns string The host required host - if any
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Return the required HTTP-methods of the route - if any.
     *
     * @returns array The HTTP-methods this route requires
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Get the path of the route.
     *
     * @returns string The path of the route
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Return a specific param requirement.
     *
     * @param string $key The name of the param to check for
     * @returns string|null The requirement or null if not found
     */
    public function getRequirement($key)
    {
        if (isset($this->requirements[$key])) {
            return $this->requirements[$key];
        }
    }

    /**
     * Return the route params requirements - if any.
     *
     * @returns array The route params requirements
     */
    public function getRequirements()
    {
        return $this->requirements;
    }

    /**
     * Return the required schemes - if any.
     *
     * @returns array The schemes of the route
     */
    public function getSchemes()
    {
        return $this->schemes;
    }

    /**
     * Check if the route has as specific attribute.
     *
     * @param string $name The name of the attribute
     * @returns boolean True if attribute is found, false if not
     */
    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * Check if a requirement is set for a route param.
     *
     * @param string $key The param key
     * @returns boolean True if requirement is set, false if not
     */
    public function hasRequirement($key)
    {
        return array_key_exists($key, $this->requirements);
    }

    /**
     * Check if a route param is valid.
     *
     * @param string $key   The param key
     * @param string $regex The param requirement as regex
     * @returns string The sanitized regex
     */
    private function sanitizeRequirement($key, $regex)
    {
        if (!is_string($regex)) {
            throw new \InvalidArgumentException(sprintf('Routing requirement for "%s" must be a string.', $key));
        }

        if (('' !== $regex) && ('^' === $regex[0])) {
            $regex = (string) substr($regex, 1);
        }

        if ('$' === substr($regex, -1)) {
            $regex = substr($regex, 0, -1);
        }

        if ('' === $regex) {
            throw new \InvalidArgumentException(sprintf('Routing requirement for "%s" cannot be empty.', $key));
        }

        return $regex;
    }

    /**
     * Serialize the route as an array.
     *
     * @return array The serialized route
     */
    public function serialize()
    {
        return serialize(array(
            'path' => $this->path,
            'host' => $this->host,
            'attributes' => $this->attributes,
            'requirements' => $this->requirements,
            'schemes' => $this->schemes,
            'methods' => $this->methods,
        ));
    }

    /**
     * Set a host.
     *
     * @param string $host The host to match
     */
    public function setHost($host)
    {
        $this->host = (string) $host;
        $this->compiled = null;
    }

    /**
     * Set one or many supported HTTP-methods.
     *
     * @param string|array $methods The supported HTTP-methods of this route
     */
    public function setMethods($methods)
    {
        $this->methods = array_map('strtoupper', (array) $methods);
        $this->compiled = null;
    }

    /**
     * Set a routes attribute (controller etc.).
     *
     * @param string $name  The name of the attribute
     * @param string $value The value of the attribute
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
        $this->compiled = null;
    }

    /**
     * Set the routes attributes (controller etc.).
     *
     * @param array $attributes The attributes to set
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = [];
        $this->addAttributes($attributes);
    }

    /**
     * Set the path for the route.
     *
     * @param string $path The path for the route
     */
    public function setPath($path)
    {
        $this->path = '/'.ltrim(trim($path), '/');
        $this->compiled = null;
    }

    /**
     * Set regex requirement for a route params.
     *
     * @param string $key   The param name
     * @param string $regex The regex to match
     */
    public function setRequirement($key, $regex)
    {
        $this->requirements[$key] = $this->sanitizeRequirement($key, $regex);
        $this->compiled = null;
    }

    /**
     * Set regex requirements for route params.
     *
     * @param array $requirements Regex requirement for the route params
     */
    public function setRequirements(array $requirements)
    {
        $this->requirements = [];
        $this->addRequirements($requirements);
    }

    /**
     * Set the schemes this route supports.
     *
     * @param string|array $schemes The schemes this route supports
     */
    public function setSchemes($schemes)
    {
        $this->schemes = array_map('strtolower', (array) $schemes);
        $this->compiled = null;
    }
}
