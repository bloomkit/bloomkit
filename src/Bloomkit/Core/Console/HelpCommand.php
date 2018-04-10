<?php

namespace Bloomkit\Core\Console;

/**
 * Definition of the "help" console command.
 */
class HelpCommand extends ConsoleCommand
{
    /**
     * Constructor.
     *
     * @param $consoleApp The ConsoleApplication to register this command to
     */
    public function __construct(ConsoleApplication $console)
    {
        parent::__construct($console, 'help');
        $this->setHelp('Displays help for a command');
        $this->setDesc('Displays help for a command');
    }

    /**
     * Command execution.
     */
    protected function execute()
    {
        $this->printHelp();

        return 0;
    }
}
