<?php
namespace Bloomkit\Core\EventManager;

class Event
{
    private $stopProcessing = FALSE;    

    private $name;     
    
    protected $eventManager;    

    /**
     * Return the status of the stopProcessing Flag
     *
     * @return boolean
     */
    public function getStopProcessing()
    {
        return $this->stopProcessing;
    }

    /**
     * Set the stopProcessing Flag
     */
    public function stopProcessing()
    {
        $this->stopProcessing = TRUE;
    }

    /**
     * Set the event manager
     *
     * @param EventManager $eventManager
     */
    public function setEventManager(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Return the event name
     *
     * @result string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the event name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
