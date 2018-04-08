<?php
namespace Bloomkit\Core\EventManager\Tests;

use Bloomkit\Core\EventManager\Event;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public function testProcessingStopped()
    {
        $event = new Event();
        $this->assertFalse($event->getStopProcessing());
    }
    
    public function testStopProcessing()
    {
        $event = new Event();
        $event->stopProcessing();
        $this->assertTrue($event->getStopProcessing());
    }
}