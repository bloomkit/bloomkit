<?php

namespace Bloomkit\Core\Console;

/**
 * Definition of a console command option.
 */
class ConsoleOption
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $shortcut;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $defaultValue;

    /**
     * @var bool
     */
    private $isRequired;

    /**
     * @var bool
     */
    private $requireValue;

    /**
     * Constructor.
     *
     * @param string $name         Name of the option
     * @param string $desription   Description of the option
     * @param string $shortcut     Shortcode ("-?")
     * @param bool   $requireValue True if the option require a value
     * @param mixed  $default      Default value
     * @param bool   $required     True if the option is required
     */
    public function __construct($name, $description = '', $shortcut = null, $requireValue = false, $default = null, $required = false)
    {
        $this->name = trim($name, '-');
        $this->shortcut = trim($shortcut, '-');
        $this->description = $description;
        $this->defaultValue = $default;
        $this->requireValue = $requireValue;
        $this->isRequired = $required;
    }

    /**
     * Returns the default value of the option.
     *
     * @return string The default value of the option
     */
    public function getDefault()
    {
        return $this->defaultValue;
    }

    /**
     * Returns the required attribute of the option.
     *
     * @return bool True if the option is required, false if not
     */
    public function getIsRequired()
    {
        return $this->isRequired;
    }

    /**
     * Returns the description of the option.
     *
     * @return string The options description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the requiredValue attribute of the option.
     *
     * @return bool True if option requires a value, false if not
     */
    public function getRequireValue()
    {
        return $this->requireValue;
    }

    /**
     * Returns the name of the option.
     *
     * @return string The name of the option
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the option-shortcut.
     *
     * @return string The shortcut of the option
     */
    public function getShortcut()
    {
        return $this->shortcut;
    }
}
