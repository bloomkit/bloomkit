<?php
namespace Bloomkit\Core\EventManager;

class Event
{
    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var string
     */
    private $name = '';     
    
    /**
     * @var bool
     */
    private $stopProcessing = FALSE;    

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
     * Return the status of the stopProcessing flag
     *
     * @return boolean
     */
    public function getStopProcessing()
    {
        return $this->stopProcessing;
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
     * Set the event name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * Set the stopProcessing flag
     */
    public function stopProcessing()
    {
        $this->stopProcessing = TRUE;
    }
}