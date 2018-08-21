<?php

namespace Bloomkit\Module\Cron\Events;

use Bloomkit\Core\EventManager\Event;

class CronEvent extends Event
{
    const CRONRUN = 'cron.run';

    public function __construct()
    {
    }

}
