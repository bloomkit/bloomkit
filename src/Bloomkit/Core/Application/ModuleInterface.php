<?php

namespace Bloomkit\Core\Application;

use Bloomkit\Core\EventManager\EventManager;

interface ModuleInterface
{
    public function getEntities();

    public function getName();

    public function getNamespace();

    public function getPath();

    public function getRoutes();

    public function initialize();

    public function registerConsoleCommands();

    public function registerEvents(EventManager $eventManager);

    public function registerSubmodules();

    public function setApplication(Application $application);

    public function shutdown();
}
