<?php

namespace Bloomkit\Core\EventManager;

class Event
{
    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var bool
     */
    private $stopProcessing = false;

    /**
     * @var mixed
     */
    private $tracerEvent;

    /**
     * @var mixed
     */
    private $tracerListener;

    /**
     * Return the data property.
     *
     * @result mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Return the event name.
     *
     * @result string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return the status of the stopProcessing flag.
     *
     * @return bool
     */
    public function getStopProcessing()
    {
        return $this->stopProcessing;
    }

    /**
     * Return the tracer event.
     *
     * @return mixed
     */
    public function getTracerEvent()
    {
        return $this->tracerEvent;
    }

    /**
     * Return the tracer listener event.
     *
     * @return mixed
     */
    public function getTracerListenerEvent()
    {
        return $this->tracerListener;
    }

    /**
     * Set the data property.
     *
     * @param mixed $data The data to set
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Set the event manager.
     *
     * @param EventManager $eventManager
     */
    public function setEventManager(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Set the event name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Set the tracer event.
     *
     * @param mixed $tracerEvent
     */
    public function setTracerEvent($tracerEvent)
    {
        $this->tracerEvent = $tracerEvent;
    }

    /**
     * Set the tracer listener event.
     *
     * @param mixed $tracerEvent
     */
    public function setTracerListenerEvent($tracerEvent)
    {
        $this->tracerEvent = $tracerListener;
    }

    /**
     * Set the stopProcessing flag.
     */
    public function stopProcessing()
    {
        $this->stopProcessing = true;
    }
}
