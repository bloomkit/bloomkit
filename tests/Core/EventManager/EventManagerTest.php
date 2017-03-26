<?php
namespace Bloomkit\Tests\Application;

use Bloomkit\Core\EventManager\EventManager;
use PHPUnit\Framework\TestCase;
use Bloomkit\Core\EventManager\Event;
use Bloomkit\Core\EventManager\EventSubscriberInterface;

class EventManagerTest extends TestCase
{
    private $eventValue;

    public function testRegisterListener()
    {
        $eventType = 'foo:event';        
        $eventManager = new EventManager();
        $this->assertFalse($eventManager->hasListeners($eventType));
        $eventManager->addListener($eventType, array($this, 'onEventForListener'));
        $this->assertTrue($eventManager->hasListeners($eventType));
    }
    
    public function testRegisterSubscriber()
    {
        $eventType = 'foo:event';
        $eventManager = new EventManager();
        $subscriber = new TestSubscriber();
        $eventManager->addSubscriber($subscriber);
        $subscriber->eventValue = false;
        $event = new Event();
        $eventManager->triggerEvent($eventType, $event);
        $this->assertTrue($subscriber->eventValue);        
    }
    
    public function testHandleEvent()
    {
        $eventType = 'foo:event';
        $eventManager = new EventManager();
        $eventManager->addListener($eventType, array($this, 'onEventForListener'));
        $this->eventValue = false;
        $event = new Event();
        $eventManager->triggerEvent($eventType, $event);        
        $this->assertTrue($this->eventValue);
    }

    public function testRemoveEvent()
    {
        $eventType = 'foo:event';
        $eventManager = new EventManager();
        $eventManager->addListener($eventType, array($this, 'onEventForListener'));
        $eventManager->removeListener($eventType, array($this, 'onEventForListener'));
        $this->eventValue = false;
        $event = new Event();
        $eventManager->triggerEvent($eventType, $event);
        $this->assertFalse($this->eventValue);
    }
    
    public function testEventPriority()
    {
        $eventType = 'foo:event';
        $eventManager = new EventManager();
        
        $this->eventValue = '';
        $eventManager->addListener($eventType, array($this, 'onEventForListenerA'), 20);
        $eventManager->addListener($eventType, array($this, 'onEventForListenerB'), 10);        
        $event = new Event();
        $eventManager->triggerEvent($eventType, $event);
        $this->assertEquals('AB', $this->eventValue);
        
        $this->eventValue = '';
        $eventManager->removeListener($eventType, array($this, 'onEventForListenerA'));
        $eventManager->removeListener($eventType, array($this, 'onEventForListenerB'));
        $eventManager->addListener($eventType, array($this, 'onEventForListenerA'), 10);
        $eventManager->addListener($eventType, array($this, 'onEventForListenerB'), 20);
        $event = new Event();
        $eventManager->triggerEvent($eventType, $event);
        $this->assertEquals('BA', $this->eventValue);        
    }
    
    
    public function onEventForListener(Event $event)
    {
        if ($event->getName() == 'foo:event')
            $this->eventValue = true;
    }
    
    public function onEventForListenerA(Event $event)
    {
        if ($event->getName() == 'foo:event')
            $this->eventValue .= 'A';
    }
    
    public function onEventForListenerB(Event $event)
    {
        if ($event->getName() == 'foo:event')
            $this->eventValue .= 'B';
    }
}

class TestSubscriber implements EventSubscriberInterface
{
    public $eventValue;
    
    
    public function onEventForListener(Event $event)
    {
        if ($event->getName() == 'foo:event')
            $this->eventValue = true;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'foo:event' => array('onEventForListener', 100)
        );
    }
}
