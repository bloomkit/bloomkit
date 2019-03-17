<?php

namespace Bloomkit\Core\Console;

use Bloomkit\Core\Console\Exceptions\InvalidCommandNameException;

/**
 * Definition of a core console command.
 */
class ConsoleCommand
{
    /**
     * The ConsoleApplication object.
     *
     * @var ConsoleApplication
     */
    protected $application;

    /**
     * Arguments accepted by the command.
     *
     * @var ConsoleArgument[]
     */
    protected $arguments = [];

    /**
     * Id of the command.
     *
     * @var string
     */
    protected $commandId;

    /**
     * Command description (for list-output).
     *
     * @var string
     */
    protected $desc;

    /**
     * Help-text.
     *
     * @var string
     */
    protected $help;

    /**
     * The console input (command line parameters).
     *
     * @var ConsoleInput
     */
    protected $input;

    /**
     * Possibility to disable commands.
     *
     * @var bool
     */
    protected $disabled = false;

    /**
     * Command name.
     *
     * @var string
     */
    protected $name;

    /**
     * Options accepted by the command.
     *
     * @var ConsoleOption[]
     */
    protected $options = [];

    /**
     * Object for console output.
     *
     * @var ConsoleOutput
     */
    protected $output;

    /**
     * Constructor.
     *
     * @param $consoleApp The console application object
     * @param $commandName The name of the command
     */
    public function __construct(ConsoleApplication $consoleApp, $commandName)
    {
        $this->application = $consoleApp;
        $this->setName($commandName);
        $this->addOption('help', '?', 'Display this help message', false, null);
    }

    /**
     * Create and add a console argument to the commands argument-list.
     *
     * @param string $name        The name of the argument
     * @param string $description The description of the argument
     * @param mixed  $default     The argument default value
     * @param bool   $isRequired  Is this argument required?
     */
    public function addArgument($name, $description = '', $default = null, $isRequired = false)
    {
        $this->arguments[] = new ConsoleArgument($name, $description, $default, $isRequired);
    }

    /**
     * Create and add a console option to the commands option-list.
     *
     * @param string      $name         The name of the option
     * @param string|null $scortcode    The shortcode of the option
     * @param string      $description  The description of the option
     * @param bool        $requireValue Does this option require a value?
     * @param mixed       $default      The options default value
     * @param bool        $isRequired   Is this option required?
     */
    public function addOption($name, $shortcut = null, $description = '', $requireValue = false, $default = null, $isRequired = false)
    {
        $this->options[] = new ConsoleOption($name, $description, $shortcut, $requireValue, $default, $isRequired);
    }

    /**
     * The execution part of the command.
     *
     * @returns int Exit code. 0 means OK, >=1 means error
     */
    protected function execute()
    {
        return 0;
    }

    /**
     * Returns the console applicatiopn.
     *
     * @return ConsoleApplication The console application object
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Returns an argument by index.
     *
     * @param int $index The name to search for
     *
     * @return ConsoleArgument|null The argument-object or null if not found
     */
    public function getArgumentByIndex($index)
    {
        if (count($this->arguments) > $index) {
            return $this->arguments[$index];
        }
    }

    /**
     * Returns the argument desc.
     *
     * @return string The argument description
     */
    public function getDesc()
    {
        return $this->desc;
    }

    /**
     * Returns the help-text for the argument.
     * If the help-text contains "%command.name%" or "%command.full_name%" they will be
     * replaced by the values of this command.
     *
     * @return string The help-text of the command
     */
    public function getHelp()
    {
        $scriptName = $this->application->getScriptName();
        $tokens = [
            '%command.name%',
            '%command.full_name%',
        ];
        $replacements = [$this->name, $scriptName.' '.$this->name];
        $helpText = str_replace($tokens, $replacements, $this->help);

        return $helpText;
    }

    /**
     * Returns the argument name.
     *
     * @return string The argument name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns an option by its name.
     *
     * @param string $name The name to search for
     *
     * @return ConsoleOption|null The option or null if not found
     */
    public function getOptionByName($name)
    {
        foreach ($this->options as $option) {
            if ($option->getName() == $name) {
                return $option;
            }
        }
    }

    /**
     * Returns an option by its short-name.
     *
     * @param string $name The shortname to search for
     *
     * @return ConsoleOption|null The option or null if not found
     */
    public function getOptionByShortname($name)
    {
        foreach ($this->options as $option) {
            if ($option->getShortcut() == $name) {
                return $option;
            }
        }
    }

    /**
     * Returns the options.
     *
     * @return ConsoleOption[] The options of this command
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Returns the number of required arguments.
     *
     * @return int The number of required arguments
     */
    public function getRequiredArgumentsCount()
    {
        $cnt = 0;
        foreach ($this->arguments as $argument) {
            if ($argument->getIsRequired()) {
                ++$cnt;
            }
        }

        return $cnt;
    }

    /**
     * Return command runtime output.
     *
     * @return string Command runtime output
     */
    protected function getOutput()
    {
        $tracer = $this->application->getTracer();
        $event = $tracer->stop('ConsoleCommand:Run');
        $duration = $event->getDuration();
        $memory = $event->getMemory() / 1024;
        $output = $this->commandId."\r\n";
        $output .= date('Y-m-d H:i:s')."\r\n";
        $output .= "runtime: $duration ms; memory-usage: $memory kb"."\r\n";

        return $output;
    }

    /**
     * Return command synopsis.
     *
     * @return string Command synopsis
     */
    public function getSynopsis()
    {
        $elements = array();

        if (count($this->options) > 0) {
            $elements[] = '[OPTIONS]';
        }

        foreach ($this->arguments as $argument) {
            if ($argument->getIsRequired()) {
                $elements[] = $argument->getName();
            } else {
                $elements[] = '['.$argument->getName().']';
            }
        }

        return trim($this->getName().' '.implode(' ', $elements));
    }

    /**
     * Returns the disabled-state of the command.
     *
     * @return bool The disable state of the command
     */
    public function isDisabled()
    {
        return $this->disabled;
    }

    /**
     * Print help-text to output.
     */
    public function printHelp()
    {
        $lines = [];
        $lines[] = $this->application->getLongVersion();
        $lines[] = '';
        $lines[] = 'Usage:';
        $lines[] = $this->getSynopsis();
        $lines[] = '';

        $maxTextWidth = 0;
        foreach ($this->arguments as $argument) {
            $name = $argument->getName();
            $textWidth = strlen($name);
            if ($textWidth > ($maxTextWidth + 2)) {
                $maxTextWidth = $textWidth + 2;
            }
        }
        if (count($this->options) > 0) {
            foreach ($this->options as $option) {
                $name = '';
                $shortcut = $option->getShortcut();
                if ('' != $shortcut) {
                    $name = '-'.$shortcut.', ';
                }
                $name .= '--'.$option->getName();
                if ($option->getRequireValue()) {
                    $name .= '=name';
                }
                $textWidth = strlen($name);
                if ($textWidth > $maxTextWidth) {
                    $maxTextWidth = $textWidth;
                }
            }
        }

        if (count($this->arguments) > 0) {
            $lines[] = 'Arguments:';
            $output = '';
            foreach ($this->arguments as $argument) {
                $defaultValue = $argument->getDefault();
                if (isset($defaultValue)) {
                    $default = sprintf(' (default: %s)', $defaultValue);
                } else {
                    $default = '';
                }
                $name = $argument->getName();
                $desc = $argument->getDescription();
                $output = sprintf(" %-${maxTextWidth}s %s%s", $name, $desc, $default);
                $lines[] = $output;
            }
            $lines[] = '';
        }

        if (count($this->options) > 0) {
            $lines[] = 'Options:';
            $output = '';
            foreach ($this->options as $option) {
                $defaultValue = $option->getDefault();
                if (isset($defaultValue)) {
                    $default = sprintf(' (default: %s)', $defaultValue);
                } else {
                    $default = '';
                }
                $name = '';
                $shortcut = $option->getShortcut();
                if ('' != $shortcut) {
                    $name = '-'.$shortcut.', ';
                }
                $name .= '--'.$option->getName();
                if ($option->getRequireValue()) {
                    $name .= '=name';
                }

                $desc = $option->getDescription();
                $output = sprintf(" %-${maxTextWidth}s %s%s", $name, $desc, $default);
                $lines[] = $output;
            }
            $lines[] = '';
        }

        $helpText = $this->getHelp();
        if ('' != $helpText) {
            $lines[] = 'Help:';
            $lines[] = ' '.$helpText;
        }
        $lines[] = '';
        $output = implode("\n", $lines);
        $this->output->writeLine($output);
    }

    /**
     * Process the console command.
     *
     * @param ConsoleInput  $input  The input to process
     * @param ConsoleOutput $output The output to write to
     */
    public function run(ConsoleInput $input, ConsoleOutput $output)
    {
        $this->input = $input;
        $this->output = $output;
        if ($this->application->getHelpStatus()) {
            $this->printHelp();

            return 0;
        }
        $input->validate();

        return $this->execute();
    }

    /**
     * Set the commands description.
     *
     * @param string $desc The description for the command
     */
    public function setDesc($desc)
    {
        $this->desc = $desc;
    }

    /**
     * Set the commands help-text.
     *
     * @param string $help The help-text for the command
     */
    public function setHelp($help)
    {
        $this->help = $help;
    }

    /**
     * Set the name of the command.
     *
     * @param string $name The command name
     */
    public function setName($name)
    {
        //check for invalid chars
        if (!preg_match('/^[^\:]+(\:[^\:]+)*$/', $name)) {
            throw new InvalidCommandNameException(sprintf('Command name "%s" is not valid.', $name));
        }
        $this->name = $name;
    }
}
