<?php

namespace Bloomkit\Modules\Cron\Console;

use Bloomkit\Core\Console\ConsoleCommand;
use Bloomkit\Modules\Cron\Events\CronEvent;

class CronRunCommand extends ConsoleCommand
{
    public function __construct($console)
    {
        parent::__construct($console, 'cron:run');
        $this->setDesc('');
        $this->setHelp('');
    }

    protected function beforeExecute()
    {
        $tracer = $this->application->getTracer();
        $tracer->start('Cron:Run');
    }

    protected function execute()
    {
        $eventManager = $this->application->getEventManager();
        $event = new CronEvent();
        $eventManager->triggerEvent(CronEvent::CRONRUN, $event);
    }

    protected function getOutput()
    {
        $tracer = $this->application->getTracer();
        $event = $tracer->stop('Cron:Run');
        $duration = $event->getDuration();
        $memory = $event->getMemory() / 1024;
        $output = $this->name."\r\n";
        $output .= date('Y-m-d H:i:s')."\r\n";
        $output .= "runtime: $duration ms; memory-usage: $memory kb"."\r\n";

        return $output;
    }
}
