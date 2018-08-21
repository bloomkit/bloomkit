<?php

namespace Bloomkit\Modules\Cron\Console;

use Bloomkit\Core\Console\ConsoleCommand;
use Bloomkit\Core\EventManager\EventManager;
use Bloomkit\Modules\Cron\Events\CronEvent;

class CronRunCommand extends ConsoleCommand
{
    public function __construct($console)
    {
        parent::__construct($console, 'cron:run');       
        $this->setDesc('');
        $this->setHelp('');        
    }
    
    protected function execute()
    {
        $eventManager = new EventManager();
        $event = new CronEvent();
        $eventManager->triggerEvent(CronEvent::CRONRUN, $event);        
    }
}