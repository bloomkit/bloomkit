<?php
namespace Bloomkit\Core\Application;

use Bloomkit\Core\EventManager\EventManager;

interface ModuleInterface
{    
    public function __construct($moduleName);
    
    public function initialize();
    
    public function shutdown();
    
    public function registerSubmodules();
    
    public function getNamespace();
    
    public function getPath();
    
    public function getName();
    
    public function registerEvents(EventManager $eventManager);
    
    public function getEntities();
    
    public function getRoutes();
    
    public function registerConsoleCommands();
    
    public function setApplication(Application $application);
}