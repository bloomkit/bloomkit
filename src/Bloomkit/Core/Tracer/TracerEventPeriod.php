<?php

namespace Bloomkit\Core\Tracer;

/**
 * Describes a time period traced by the Tracer with the current memory consumption.
 */
class TracerEventPeriod
{
    /**
     * @var int
     */
    private $end;

    /**
     * @var int
     */
    private $memory;

    /**
     * @var int
     */
    private $start;

    /**
     * Constructor.
     *
     * @param int    $start    A start-time in milliseconds
     * @param int    $start    An end-time in milliseconds
     * @param string $category Optional category of the event to trace (e.g. "database")
     */
    public function __construct($start, $end)
    {
        $this->start = (int)$start;
        $this->end = (int)$end;
        $this->memory = memory_get_usage(true);
    }

    /**
     * Returns the start value.
     *
     * @return int The start value in milliseconds
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Returns the end value.
     *
     * @return int The end value in milliseconds
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Returns the duration (end-start).
     *
     * @return int The duration in milliseconds
     */
    public function getDuration()
    {
        return $this->end - $this->start;
    }

    /**
     * Returns the memory-consumption.
     *
     * @return int The memory-consumption at the time, this period is created
     */
    public function getMemory()
    {
        return $this->memory;
    }
}
