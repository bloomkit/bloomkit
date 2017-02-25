<?php

namespace Bloomkit\Tests\Application;

use Bloomkit\Core\EventManager\Event;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    protected $event;

    protected function setUp()
    {
        $this->event = new Event();
    }
    
    protected function tearDown()
    {
        $this->event = null;
    }
    
    public function testProcessingStopped()
    {
        $this->assertFalse($this->event->getStopProcessing());
    }
    
    public function testStopProcessing()
    {
        $this->event->stopProcessing();
        $this->assertTrue($this->event->getStopProcessing());
    }
}