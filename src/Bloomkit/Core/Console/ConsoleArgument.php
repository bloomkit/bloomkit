<?php

namespace Bloomkit\Core\Console;

/**
 * Definition of a console argument.
 */
class ConsoleArgument
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * @var string
     */
    private $description;

    /**
     * @var bool
     */
    private $isRequired;

    /**
     * Constructor.
     *
     * @param string $name        The argument name
     * @param string $description The argument description
     * @param mixed  $default     The default value of the argument
     * @param bool   $isRequired  True if the argument is required
     */
    public function __construct($name, $description = '', $default = null, $isRequired = false)
    {
        $this->name = $name;
        $this->defaultValue = $default;
        $this->isRequired = $isRequired;
        $this->description = $description;
    }

    /**
     * Returns the default value.
     *
     * @return mixed Default value of the argument
     */
    public function getDefault()
    {
        return $this->defaultValue;
    }

    /**
     * Return the argument description.
     *
     * @return string The argument description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Gibt das isRequired-Flag zurück.
     *
     * @return bool True if argument is required, false if not
     */
    public function getIsRequired()
    {
        return $this->isRequired;
    }

    /**
     * Returns the argument name.
     *
     * @return string The name of the argument
     */
    public function getName()
    {
        return $this->name;
    }
}
