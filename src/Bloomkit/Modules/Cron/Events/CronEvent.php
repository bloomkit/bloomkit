<?php

namespace Bloomkit\Modules\Cron\Events;

use Bloomkit\Core\EventManager\Event;

class CronEvent extends Event
{
    const CRONRUN = 'bloomkit.cron.run';

    public function __construct()
    {
    }
}
