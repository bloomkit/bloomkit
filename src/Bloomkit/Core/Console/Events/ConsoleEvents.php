<?php

namespace Bloomkit\Core\Console\Events;

use Bloomkit\Core\EventManager\Event;

class ConsoleEvents extends Event
{
    const REGISTEROPTIONS = 'console.registeroptions';
    const CONSOLERUN = 'console.run';
    const CONSOLEINIT = 'console.init';

    public function __construct()
    {
    }
}
