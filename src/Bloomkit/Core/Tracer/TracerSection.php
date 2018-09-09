<?php

namespace Bloomkit\Core\Tracer;

/**
 * Describes a tracing section. A section may have multiple events.
 */
class TracerSection
{
    /**
     * @var TracerEvent[]
     */
    private $events = [];

    /**
     * Return an event by its name.
     *
     * @return Event|null The event or null if not found
     */
    public function getEvent($name)
    {
        if (!isset($this->events[$name])) {
            return null;
        }

        return $this->events[$name];
    }

    /**
     * Return all events.
     *
     * @return array
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Check if an event is started.
     *
     * @param string $name Name of the event to check
     *
     * @return bool Returns true if started, false if not.
     *
     * @throws LogicException If event does not exist
     */
    public function isEventStarted($name)
    {
        if (!isset($this->events[$name])) {
            throw new \LogicException(sprintf('Event "%s" not found.', $name));
        }
        return isset($this->events[$name]) && $this->events[$name]->isStarted();
    }

    /**
     * Start and return an event with a specific name and category.
     *
     * @param string name The Name of the event to stop
     * @param string $category  Optional category of the event to trace (e.g. "database")
     *
     * @return TracerEventPeriod The new period
     */
    public function startEvent($name, $category)
    {
        if (!isset($this->events[$name])) {
            $this->events[$name] = new TracerEvent(microtime(true) * 1000, $category);
        }
        $this->events[$name]->start();

        return $this->events[$name];
    }

    /**
     * Stop and return an event with a specific name.
     *
     * @param string name The Name of the event to stop
     *
     * @return TracerEventPeriod The new period
     *
     * @throws LogicException If event does not exist
     */
    public function stopEvent($name)
    {
        if (!isset($this->events[$name])) {
            throw new \LogicException(sprintf('Event "%s" not found.', $name));
        }
        $this->events[$name]->stop();

        return $this->events[$name];
    }
}
