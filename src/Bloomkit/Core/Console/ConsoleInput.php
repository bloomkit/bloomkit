<?php

namespace Bloomkit\Core\Console;

use Bloomkit\Core\Console\Events\ConsoleEvents;
use Bloomkit\Core\EventManager\RepositoryEvent;

/**
 * Class for handling command line inputs.
 */
class ConsoleInput
{
    /**
     * Contains all application-options.
     *
     * @var ConsoleOption[];
     */
    private $applicationOptions = [];

    /**
     * Command line arguments.
     *
     * @var ConsoleArgument[]
     */
    private $arguments = [];

    /**
     * Called console command.
     *
     * @var ConsoleCommand
     */
    private $command;

    /**
     * The console application.
     *
     * @var ConsoleApplication
     */
    private $application;

    /**
     * Contains all command-options.
     *
     * @var ConsoleOption[];
     */
    private $options = [];

    /**
     * Contains a list of all command line parameters.
     *
     * @var array
     */
    private $params = [];

    /**
     * Constructor.
     *
     * @param ConsoleApplication $consoleApp The console application object
     */
    public function __construct(ConsoleApplication $consoleApp)
    {
        // Get command-line parameters
        $params = $_SERVER['argv'];

        // Remove first entry (the file-name)
        array_shift($params);
        $this->params = $params;
        $this->application = $consoleApp;

        $eventManager = $this->application->getEventManager();
        $event = new RepositoryEvent();
        $eventManager->triggerEvent(ConsoleEvents::REGISTEROPTIONS, $event);
        $this->applicationOptions = array_merge($this->applicationOptions, $event->getRepository()->getItems());

        // Parse arguments and options
        $this->parse();
    }

    /**
     * Add an argument to the argument-list.
     *
     * @param mixed $value Argument to add
     */
    private function addArgument($value)
    {
        $argCnt = count($this->arguments);
        $argument = $this->command->getArgumentByIndex($argCnt);
        if (is_null($argument)) {
            throw new \InvalidArgumentException('Too many arguments');
        }
        $this->arguments[$argument->getName()] = $value;
    }

    /**
     * Add a provided option by long-style (e.g. --name) to the option-list.
     *
     * @param string $name  Name of the option in long-style (e.g. --name)
     * @param mixed  $value Option value
     */
    private function addLongOption($name, $value = null)
    {
        $option = null;
        foreach ($this->applicationOptions as $appOption) {
            if ($appOption->getName() == $name) {
                $option = $appOption;
            }
        }
        if (is_null($option)) {
            $option = $this->command->getOptionByName($name);
        }
        if (is_null($option)) {
            throw new \InvalidParameterException(sprintf('Option "--%s" does not exist.', $name));
        }
        if ((is_null($value)) && ($option->getRequireValue())) {
            throw new \InvalidArgumentException(sprintf('Option "--%s" requires a value.', $name));
        }
        $this->options[$option->getName()] = $value;
    }

    /**
     * Add a provided option by short-style (e.g. -n) to the option-list.
     *
     * @param string $name  Name of the option in short-style (e.g. -n)
     * @param mixed  $value Option value
     */
    private function addShortOption($name, $value = null)
    {
        $option = null;
        foreach ($this->applicationOptions as $appOption) {
            if ($appOption->getShortcut() == $name) {
                $option = $appOption;
            }
        }
        if (is_null($option)) {
            $option = $this->command->getOptionByShortname($name);
        }
        if (is_null($option)) {
            throw new \InvalidArgumentException(sprintf('Option "-%s" does not exist.', $name));
        }
        if ((is_null($value)) && ($option->getRequireValue())) {
            throw new \InvalidArgumentException(sprintf('Option "-%s" requires a value.', $name));
        }
        $this->options[$option->getName()] = $value;
    }

    /**
     * Returns an applicationOption by its name.
     *
     * @param string $name The name to search for
     *
     * @return ConsoleOption|null The option or null if not found
     */
    public function getApplicationOptionByName($name)
    {
        foreach ($this->applicationOptions as $option) {
            if ($option->getName() == $name) {
                return $option;
            }
        }
    }

    /**
     * Returns an applicationOption by its short-name.
     *
     * @param string $name The shortname to search for
     *
     * @return ConsoleOption|null The option or null if not found
     */
    public function getApplicationOptionByShortname($name)
    {
        foreach ($this->options as $option) {
            if ($option->getShortcut() == $name) {
                return $option;
            }
        }
    }

    /**
     * Returns an argument value by its name.
     *
     * @param string $name Name of the argument
     *
     * @return mixed Argument value
     */
    public function getArgumentValueByName($name)
    {
        if (isset($this->arguments[$name])) {
            return $this->arguments[$name];
        }
    }

    /**
     * Returns the command to be called.
     *
     * @return ConsoleCommand|null The command to be called or null if not provided
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Returns an option by its name.
     *
     * @param string $name Name of the option
     *
     * @return mixed Value of the option or default-value if null
     */
    public function getOptionValueByName($name)
    {
        $value = null;
        $option = $this->command->getOptionByName($name);
        if (is_null($option)) {
            throw new \InvalidArgumentException(sprintf('Option "--%s" does not exist.', $name));
        }
        if (isset($this->options[$name])) {
            $value = $this->options[$name];
        }
        if (is_null($value)) {
            $value = $option->getDefault();
        }

        return $value;
    }

    /**
     * Check if argument is set in input.
     *
     * @param string $name Name of the argument to check
     *
     * @return bool True if argument is provided, false if not
     */
    public function hasArgument($name)
    {
        return isset($this->arguments[$name]);
    }

    /**
     * Check if option is is set in input.
     *
     * @param string $name Name of the option to check
     *
     * @return bool True if option is provided, false if not
     */
    public function hasOption($name)
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * Checks if one ore more of the provided params is set in input.
     *
     * @param array $valueList List of params to check
     *
     * @return bool Returns true if at least one of the provided params is set in input, false if not
     */
    public function hasParam(array $paramList)
    {
        $paramList = (array) $paramList;
        foreach ($this->params as $param) {
            foreach ($paramList as $value) {
                if (($param === $value) || (strpos($param, $value.'=') > 0)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Parse provided command-line parameters.
     */
    public function parse()
    {
        //Finding the command
        $commandName = array_shift($this->params);
        $this->command = $this->application->getCommandByName($commandName);

        //if this failes, use the "list" command instead and return
        if (is_null($this->command)) {
            $this->command = $this->application->getCommandByName('list');

            return;
        }

        // Parse params
        $params = $this->params;

        foreach ($params as $param) {
            if (0 === strpos($param, '--')) {
                $this->parseLongOption($param);
            } elseif (0 === strpos($param, '-')) {
                $this->parseShortOption($param);
            } else {
                $this->addArgument($param);
            }
        }
    }

    /**
     * Parses a provided command-option in long-style.
     *
     * @param string option The complete text of the provied option (e.g. --name=value)
     */
    private function parseLongOption($option)
    {
        $option = trim(substr($option, 2));
        $value = null;
        $pos = strpos($option, '=');
        if (false !== $pos) {
            $value = trim(substr($option, $pos + 1));
            $option = trim(substr($option, 0, $pos));
        }
        $this->addLongOption($option, $value);
    }

    /**
     * Parses a provided command-option in short-style.
     *
     * @param string option The complete text of the provied option (e.g. -n=value)
     */
    private function parseShortOption($option)
    {
        $option = trim(substr($option, 1));
        $value = null;
        $pos = strpos($option, '=');
        if (false !== $pos) {
            $value = trim(substr($option, $pos + 1));
            $option = trim(substr($option, 0, $pos));
        }
        $this->addShortOption($option, $value);
    }

    /**
     * Checks if all required command-options are provided.
     *
     * @return bool True if required parameters matched, false if not
     */
    public function validate()
    {
        $reqArgCnt = $this->command->getRequiredArgumentsCount();
        if (count($this->arguments) < $reqArgCnt) {
            throw new \InvalidArgumentException('Not enough arguments');
        }
        $options = $this->command->getOptions();
        foreach ($options as $option) {
            //iterate through all required values without a default-value -
            //these MUST be provided by command line
            if (($option->getIsRequired()) && (is_null($option->getDefault()))) {
                $optionName = $option->getName();
                if (false == isset($this->options[$optionName])) {
                    throw new \InvalidArgumentException(sprintf('Option "--%s" is required.', $optionName));
                }
            }
        }
    }
}
