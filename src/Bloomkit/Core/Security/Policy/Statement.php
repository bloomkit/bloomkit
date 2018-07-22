<?php

namespace Bloomkit\Core\Security\Policy;

/**
 * A policy statement defines what is allowed or denied to a specific resource.
 */
class Statement
{
    const EFFECT_DENY = 0;

    const EFFECT_ALLOW = 1;

    /**
     * @var array
     */
    private $actions = [];

    /**
     * @var int
     */
    private $effect = self::EFFECT_DENY;

    /**
     * @var array
     */
    private $resources = [];

    /**
     * @var string
     */
    private $sid = '';

    /**
     * Constructor.
     *
     * @param string $sid       A unique id for the Statement
     * @param int    $effect    The effect this Statement will cause
     * @param array  $actions   A list of actions this Statement will cover
     * @param array  $resources A list of resources this Statement will cover
     */
    public function __construct($sid = '', $effect = self::EFFECT_DENY, array $actions = [], array $resources = [])
    {
        $this->sid = $sid;
        $this->effect = $effect;
        $this->actions = $actions;
        $this->resources = $resources;
    }

    /**
     * Returns the actions covered by this Statement.
     *
     * @return array The actions of this Statement
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * Returns the resources covered by this Statement.
     *
     * @return array The resources of this Statement
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Set the actions of this Statement.
     *
     * @param array $actions A list of action-strings
     */
    public function setActions(array $actions)
    {
        $this->actions = $actions;
    }

    /**
     * Set the resources of this Statement.
     *
     * @param array $resources A list of resource-strings
     */
    public function setResources(array $resources)
    {
        $this->resources = $resources;
    }
}
