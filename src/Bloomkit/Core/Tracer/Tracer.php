<?php

namespace Bloomkit\Core\Tracer;

/**
 * The Tracer is used for performance measurement and runtime analtics.
 */
class Tracer
{
    /**
     * @var TracerSection[]
     */
    private $sections = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->sections['root'] = new TracerSection();
    }

    /**
     * Start an event in the current section.
     *
     * @param string $name     Name of the action to trace
     * @param string $category Optional category of the action to trace (e.g. "database")
     *
     * @return TracerEvent The started event
     */
    public function start($name, $category = null)
    {
        return end($this->sections)->startEvent($name, $category);
    }

    /**
     * Stop an event.
     *
     * @param string $name Name of the activity to stop
     *
     * @return TracerEvent The stopped event
     */
    public function stop($name)
    {
        return end($this->sections)->stopEvent($name);
    }
}
