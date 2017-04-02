<?php
namespace Bloomkit\Core\EventManager;

interface EventTracerInterface
{
    public function onAfterEvent($eventName, Event $event);
    
    public function onAfterEventListener($listenerName, $eventName, Event $event);
        
    public function onBeforeEvent($eventName, Event $event);
    
    public function onBeforeEventListener($listenerName, $eventName, Event $event);
}
