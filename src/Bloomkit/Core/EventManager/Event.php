<?php
namespace Bloomkit\Core\EventManager;

class Event
{
    /**
     * @var bool
     */
    private $stopProcessing = FALSE;    

    /**
     * @var string
     */
    private $name = '';     
    
    /**
     * @var EventManager
     */
    protected $eventManager;    

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
     * Set the stopProcessing flag
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
