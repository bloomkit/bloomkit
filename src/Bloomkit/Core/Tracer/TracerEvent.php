<?php

namespace Bloomkit\Core\Tracer;

/**
 * Describes an event to trace. An event may have multiple periods.
 */
class TracerEvent
{
    /**
     * @var string
     */
    private $category;

    /**
     * @var float
     */
    private $originTime;

    /**
     * @var TracerEventPeriod[]
     */
    private $periods = [];

    /**
     * @var float[]
     */
    private $started = [];

    /**
     * Constructor.
     *
     * @param float $originTime  The unix-time in miliseconds, this event is created
     * @param string $category  Optional category of the event to trace (e.g. "database")
     */
    public function __construct($originTime, $category = 'default')
    {
        $this->originTime = round($originTime, 1);
        $this->category = $category;
    }

    /**
     * Return the event category.
     *
     * @return string The category on this event
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Return the duration over all periods on this event.
     *
     * @return int Duration in milliseconds
     */
    public function getDuration()
    {
        $periods = $this->periods;
        $stoppedPeriods = count($periods);
        $leftPeriods = count($this->started) - $stoppedPeriods;

        for ($i = 0; $i < $leftPeriods; ++$i ) {
            $index = $stoppedPeriods + $i;
            $periods[] = new TracerEventPeriod($this->started[$index], $this->getRuntime());
        }

        $duration = 0;
        foreach ($periods as $period) {
            $duration += $period->getDuration();
        }

        return $duration;
    }

    /**
     * Returns the end time of this event (relative to originTime).
     *
     * @return int The end time in milliseconds
     */
    public function getEndTime()
    {
        $count = count($this->periods);
        if ($count == 0) {
            return 0;
        }

        return $this->periods[$count - 1]->getEndTime();
    }

    /**
     * Return the highest memory consumption over all periods on this event.
     *
     * @return int Memory consumption on this event
     */
    public function getMemory()
    {
        $memory = 0;
        foreach ($this->periods as $period) {
            if ($period->getMemory() > $memory) {
                $memory = $period->getMemory();
            }
        }

        return $memory;
    }

    /**
     * Returns the originTime of this event.
     *
     * @return int The originTime in milliseconds
     */
    public function getOriginTime()
    {
        return (int) $this->originTime;
    }

    /**
     * Return all Periods on this event.
     *
     * @return array The periods of this event
     */
    public function getPeriods()
    {
        return $this->periods;
    }

    /**
     * Return current runtime (now - originTime).
     *
     * @return float Current runtime in miliseconds
     */
    protected function getRuntime()
    {
        return round(microtime(true) * 1000 - $this->originTime, 1);
    }

    /**
     * Returns the start time of this event (relative to originTime).
     *
     * @return int The start time in milliseconds
     */
    public function getStartTime()
    {
        $start = 0;
        if (isset($this->periods[0])) {
            $start = (int) $this->periods[0]->getStart();
        } elseif (isset($this->started[0])) {
            $start = (int) $this->started[0];
        }

        return $start;
    }

    /**
     * Check if event is started.
     *
     * @return bool Returns true if started, false if not.
     */
    public function isStarted()
    {
        return count($this->started) > 0;
    }

    /**
     * Shortcode for stop & start.
     */
    public function lap()
    {
        $this->stop();
        $this->start();
    }

    /**
     * Start a new measurement.
     */
    public function start()
    {
        $this->started[] = $this->getRuntime();
    }

    /**
     * Stop the current measurement and add a new period.
     *
     * @return TracerEventPeriod The new period
     */
    public function stop()
    {
        if (count($this->started) == 0) {
            throw new \LogicException('there are no started events');
        }
        $this->periods[] = new TracerEventPeriod(array_pop($this->started), $this->getRuntime());
    }

    /**
     * Stop all periods.
     */
    public function stopAll()
    {
        while (count($this->started)) {
            $this->stop();
        }
    }
}
