<?php

namespace Bloomkit\Core\Console;

/**
 * Definition of the "list" console command
 * Shows a list of all registered commands.
 */
class ListCommand extends ConsoleCommand
{
    /**
     * Constructor.
     *
     * @param $consoleApp The ConsoleApplication to register this command to
     */
    public function __construct(ConsoleApplication $consoleApp)
    {
        parent::__construct($consoleApp, 'list');
        $this->setDesc('Lists all available commands');
        $this->setHelp("The %command.name% command lists all available commands: \r\n\r\n  php %command.full_name%");
    }

    /**
     * Helper-function for sorting the command list.
     */
    private function sortCompare(ConsoleCommand $a, ConsoleCommand $b)
    {
        return strcmp($a->getName(), $b->getName());
    }

    /**
     * Command execution.
     */
    protected function execute()
    {
        $console = $this->getApplication();
        $commands = $console->getCommandList();
        usort($commands, array($this, 'sortCompare'));

        $lines[] = $console->getLongVersion();
        $lines[] = '';
        $lines[] = 'Available commands:';

        $maxTextWidth = 0;
        foreach ($commands as $command) {
            $name = $command->getName();
            $textWidth = strlen($name);
            if ($textWidth > $maxTextWidth) {
                $maxTextWidth = $textWidth;
            }
        }

        foreach ($commands as $command) {
            $name = $command->getName();
            $desc = $command->getDesc();
            $lines[] = sprintf(" %-${maxTextWidth}s %s", $name, $desc);
        }
        $lines[] = '';

        $output = implode("\n", $lines);
        $this->output->writeLine($output);

        return 0;
    }
}
