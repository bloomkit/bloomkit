<?php
namespace Bloomkit\Core\EventManager;

use Bloomkit\Core\EventManager\EventSubscriberInterface;

class EventManager
{
    /**
     * @var array
     */
    private $listeners = [];
    
    /**
     * @var array
     */
    private $sorted = [];   
    
    /**
     * Add a listener for an event
     *
     * @param string    $eventName  The event to listen on
     * @param callable  $listener   The listener to call
     * @param int       $priority   Higher value means the event is triggered earlier
     *
     * @return array
     */
    public function addListener($eventName, $listener, $priority = 0)
    {
        $this->listeners[$eventName][$priority][] = $listener;
        unset($this->sorted[$eventName]);
    }
    
    /**
     * Another way to register listeners is by adding a subscriber
     *
     * @param EventSubscriberInterface $subscriber
     */
    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        foreach ($subscriber->getSubscribedEvents() as $eventName => $params) {
            if (is_string($params)) {
                $this->addListener($eventName, array($subscriber, $params));
            } elseif (is_string($params[0])) {
                $this->addListener($eventName, array($subscriber, $params[0]), isset($params[1]) ? $params[1] : 0);
            } else {
                foreach ($params as $listener) {
                    $this->addListener($eventName, array($subscriber, $listener[0]), isset($listener[1]) ? $listener[1] : 0);
                }
            }
        }
    }
    
    /**
     * Return a sorted array of listeners for a specific event - or all events if eventName is null
     * 
     * @param string|null $eventName    
     * 
     * @return array
     */
    public function getListeners($eventName = null)
    {
        if (! is_null($eventName)) {
            if (! isset($this->sorted[$eventName]))
                $this->sortListeners($eventName);
            return $this->sorted[$eventName];
        }
    
        foreach (array_keys($this->listeners) as $eventName) {
            if (! isset($this->sorted[$eventName]))
                $this->sortListeners($eventName);
        }
        return $this->sorted;
    }
    
    /**
     * Check if any listeners are registered to an event
     * 
     * @param string|null $eventName    The name of the event
     * 
     * @return bool true if the event has listeners, false if not
     */
    public function hasListeners($eventName = null)
    {
        if (count($this->getListeners($eventName))>0)
            return true;
        return false;
    }
    
    /**
     * Remove a specific event-listener
     *
     * @param string    $eventName  The event to remove from
     * @param callable  $listener   The listener to remove
     */
    public function removeListener($eventName, $listener)
    {
        if (! isset($this->listeners[$eventName]))
            return;
    
        foreach ($this->listeners[$eventName] as $priority => $listeners) {
            if (false !== ($key = array_search($listener, $listeners, true))){                
                unset($this->listeners[$eventName][$priority][$key]);
                unset($this->sorted[$eventName]);
            }
        }
    }
    
    /**
     * Sort the listeners for an event by descending priority     
     */
    private function sortListeners($eventName)
    {
        $this->sorted[$eventName] = [];
        if (isset($this->listeners[$eventName])) {
            krsort($this->listeners[$eventName]);
            $this->sorted[$eventName] = call_user_func_array('array_merge', $this->listeners[$eventName]);
        }
    }

    /**
     * Call all listeners registered for a specific event
     * 
     * @param string $eventName     The name of the event to trigger
     * @param Event  $event         The event to pass to the listener 
     * 
     * @return Event
     */
    public function triggerEvent($eventName, Event $event = null)
    {
        if (is_null($event))
            $event = new Event();
        
        $event->setEventManager($this);
        $event->setName($eventName);
    
        if (! isset($this->listeners[$eventName]))
            return $event;
    
        $listeners = $this->getListeners($eventName);

        foreach ($listeners as $listener) {
            call_user_func($listener, $event);      
            if ($event->getStopProcessing())
                break;
        }
        
        return $event;
    }
}
