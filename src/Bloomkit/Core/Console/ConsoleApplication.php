<?php

namespace Bloomkit\Core\Console;

use Bloomkit\Core\Application\Application;
use Bloomkit\Core\Module\ModuleInterface;
use Bloomkit\Core\EventManager\Event;
use Bloomkit\Core\Console\Events\ConsoleEvents;
use Bloomkit\Core\Console\Events\ConsoleRunEvent;

/**
 * Application class for building console applications.
 */
class ConsoleApplication extends Application
{
    /**
     * Array of registered console-commands.
     *
     * @var array
     */
    private $commands = [];

    /**
     * Is set to true when the -h / --help option is used.
     *
     * @var bool
     */
    private $helpStatus = false;

    /**
     * The name of the console script (filename).
     *
     * @var string
     */
    private $scriptName;

    /**
     * {@inheritdoc}
     */
    public function __construct($appName, $appVersion, $basePath = null, array $config = [])
    {
        parent::__construct($appName, $appVersion, $basePath, $config);
        $this->scriptName = $_SERVER['PHP_SELF'];

        //Register general console commands
        $consoleCommand = new HelpCommand($this);
        $this->registerCommand($consoleCommand);

        $consoleCommand = new ListCommand($this);
        $this->registerCommand($consoleCommand);
    }

    /**
     * Returns a console command by name.
     *
     * @param string $commandName Name of the command
     *
     * @return ConsoleCommand|null The matching command or null if not found
     */
    public function getCommandByName($commandName)
    {
        if (isset($this->commands[$commandName])) {
            return $this->commands[$commandName];
        }
    }

    /**
     * Returns the list of registered commands.
     *
     * @return ConsoleCommand[] List of registered commands
     */
    public function getCommandList()
    {
        return $this->commands;
    }

    /**
     * Returns the help status.
     *
     * @return bool True if -? or --help is used, else false
     */
    public function getHelpStatus()
    {
        return $this->helpStatus;
    }

    /**
     * Returns the scriptName.
     *
     * @return string The scriptName
     */
    public function getScriptName()
    {
        return $this->scriptName;
    }

    /**
     * Register a console-command on the application.
     *
     * @param ConsoleCommand $command The console-command to register
     */
    public function registerCommand(ConsoleCommand $command)
    {
        $className = 'Bloomkit\Core\Console\ConsoleCommand';
        if (($command instanceof $className) || (is_subclass_of($command, $className))) {
            $this->commands[$command->getName()] = $command;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function registerModule(ModuleInterface $module)
    {
        parent::registerModule($module);
        $module->registerConsoleCommands();
    }

    /**
     * Start the application.
     *
     * @param ConsoleInput|null  $input  The input to parse (if null, it will be created)
     * @param ConsoleOutput|null $output The object to write output to (if null, it will be created)
     */
    public function run(ConsoleInput $input = null, ConsoleOutput $output = null)
    {
        try {
            foreach ($this->modules as $module) {
                $module->onModulesLoaded();
            }

            // Create output object if not provided
            if (false == isset($output)) {
                $output = new ConsoleOutput($this);
            }

            // Create input object if not provided
            if (false == isset($input)) {
                $input = new ConsoleInput($this);
            }

            // Trigger ConsoleRun Event
            $eventManager = $this->getEventManager();
            $event = new ConsoleRunEvent($input, $output);
            $event->setData($input);
            $eventManager->triggerEvent(ConsoleEvents::CONSOLERUN, $event);
            if ($event->stopProcessing()) {
                echo $output->getOutputBuffer();

                return 0;
            }

            // Check for version request
            if ($input->hasParam(['--version', '-V'])) {
                echo $this->getLongVersion()."\r\n";

                return 0;
            }

            // Check for help request
            if ($input->hasParam(['--help', '-?'])) {
                $this->helpStatus = true;
            } else {
                $this->helpStatus = false;
            }

            // Find command - if this failes, use "list" instead
            $command = $input->getCommand();
            if (is_null($command)) {
                $command = $this->getCommandByName('list');
            }

            // Run command
            return $command->run($input, $output);
        } catch (\Exception $e) {
            echo "\n\nError: ".$e->getMessage()."\n";
            exit(1);
        }
    }

    /**
     * Set the scriptName (the file running this application).
     *
     * @param string $name The scriptName
     */
    public function setScriptName($name)
    {
        $this->scriptName = $name;
    }
}
