<?php

namespace Bloomkit\Core\Routing;

class CompiledRoute
{
    /**
     * @var string
     */
    private $hostRegex;

    /**
     * @var array
     */
    private $hostTokens;

    /**
     * @var array
     */
    private $hostVariables;

    /**
     * @var array
     */
    private $pathVariables;

    /**
     * @var string
     */
    private $regex;

    /**
     * @var string
     */
    private $staticPrefix;

    /**
     * @var array
     */
    private $tokens;

    /**
     * @var array
     */
    private $variables;

    /**
     * Constructor.
     *
     * @param string      $staticPrefix  The static prefix of the compiled route
     * @param string      $regex         The regular expression to use to match this route
     * @param array       $tokens        An array of tokens to use to generate URL for this route
     * @param array       $pathVariables An array of path variables
     * @param string|null $hostRegex     Host regex
     * @param array       $hostTokens    Host tokens
     * @param array       $hostVariables An array of host variables
     * @param array       $variables     An array of variables (variables defined in the path and in the host patterns)
     */
    public function __construct($staticPrefix, $regex, array $tokens, array $pathVariables, $hostRegex = null, array $hostTokens = array(), array $hostVariables = array(), array $variables = array())
    {
        $this->staticPrefix = (string) $staticPrefix;
        $this->regex = $regex;
        $this->tokens = $tokens;
        $this->pathVariables = $pathVariables;
        $this->hostRegex = $hostRegex;
        $this->hostTokens = $hostTokens;
        $this->hostVariables = $hostVariables;
        $this->variables = $variables;
    }

    /**
     * Returns the host regex.
     *
     * @return string|null
     */
    public function getHostRegex()
    {
        return $this->hostRegex;
    }

    /**
     * Returns the host tokens.
     *
     * @return array
     */
    public function getHostTokens()
    {
        return $this->hostTokens;
    }

    /**
     * Returns the host variables.
     *
     * @return array
     */
    public function getHostVariables()
    {
        return $this->hostVariables;
    }

    /**
     * Returns the path variables.
     *
     * @return array
     */
    public function getPathVariables()
    {
        return $this->pathVariables;
    }

    /**
     * Returns the regex.
     *
     * @return string
     */
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * Returns the static prefix.
     *
     * @return string
     */
    public function getStaticPrefix()
    {
        return $this->staticPrefix;
    }

    /**
     * Returns the tokens.
     *
     * @return array
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * Returns the variables.
     *
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }
}
