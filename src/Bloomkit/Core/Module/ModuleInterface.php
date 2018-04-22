<?php

namespace Bloomkit\Core\Module;

use Bloomkit\Core\EventManager\EventManager;

/**
 * Describes all functions a bloomkit module implementation must provide.
 */
interface ModuleInterface
{
    /**
     * Get all entities this module provides and return them.
     *
     * @return Collection A collection of all entities this module provides
     */
    public function getEntities();

    /**
     * Returns the name of this module.
     *
     * @return string The name of this module
     */
    public function getName();

    /**
     * Returns the namespace of this module.
     *
     * @return string The namespace of this module
     */
    public function getNamespace();

    /**
     * Returns the path of this module.
     *
     * @return string The path of this module
     */
    public function getPath();

    /**
     * Get all routes this module provides and return them.
     *
     * @return RouteCollection A collection of all routes this module provides
     */
    public function getRoutes();

    /**
     * Lifecycle event. Every module is initialized during registration.
     * Application is already set here.
     */
    public function initialize();

    /**
     * Register all console commands of this module.
     */
    public function registerConsoleCommands();

    /**
     * Register events this module should listen for on the EventManager.
     *
     * @param EventManager $eventManager EventManager to register events to
     */
    public function registerEvents(EventManager $eventManager);

    /**
     * Register further submodules of this module.
     * Called during registration.
     */
    public function registerSubmodules();

    /**
     * Set the Application object.
     *
     * @param Application $application The application object
     */
    public function setApplication(Application $application);

    /**
     * If the shutdown-function of the application is called, this event is fired for all registered modules.
     */
    public function shutdown();
}
