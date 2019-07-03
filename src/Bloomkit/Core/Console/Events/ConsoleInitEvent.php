<?php

namespace Bloomkit\Core\Console\Events;

use Bloomkit\Core\EventManager\Event;
use Bloomkit\Core\Console\ConsoleInput;
use Bloomkit\Core\Console\ConsoleOutput;

class ConsoleInitEvent extends Event
{
    /**
     * @var ConsoleInput
     */
    protected $input;

    /**
     * @var ConsoleOutput
     */
    protected $output;

    public function __construct(ConsoleInput $input, ConsoleOutput $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    /**
     * Return the console input.
     *
     * @result ConsoleInput
     */
    public function getConsoleInput()
    {
        return $this->input;
    }

    /**
     * Return the console output.
     *
     * @result ConsoleOutput
     */
    public function getConsoleOutput()
    {
        return $this->output;
    }
}
