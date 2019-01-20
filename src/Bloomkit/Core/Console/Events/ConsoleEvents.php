<?php

namespace Bloomkit\Core\Console\Events;

use Bloomkit\Core\EventManager\Event;

class ConsoleEvents extends Event
{
    const REGISTEROPTIONS = 'console.registeroptions';
    const CONSOLERUN = 'console.run';

    public function __construct()
    {
    }
}
