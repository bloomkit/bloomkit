<?php

namespace Bloomkit\Core\Application;

use Bloomkit\Core\Utilities\Collection;
use Bloomkit\Core\EventManager\EventManager;
use Bloomkit\Core\Routing\RouteCollection;

abstract class Module implements ModuleInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var \ReflectionObject
     */
    protected $reflection;

    /**
     * @var Application
     */
    protected $application;

    /**
     * Constuctor.
     *
     * @param string $moduleName The name of the module
     */
    public function __construct($moduleName)
    {
        $this->name = $moduleName;
    }

    /**
     * Get all entities this module provides and return them.
     *
     * @return Collection A collection of all entities this module provides
     */
    public function getEntities()
    {
        $result = new Collection();
        $entityDir = $this->getPath().'/Entities';
        if (is_dir($entityDir)) {
            $entityFiles = glob($entityDir.'/*Entity.php');

            foreach ($entityFiles as $fileName) {
                require_once $fileName;
                $baseName = basename($fileName, '.php');
                $className = $this->getNamespace().'\\Entities\\'.ucfirst($baseName);
                if ((class_exists($className)) && (is_subclass_of($className, 'Bloomkit\Core\Entities\Descriptor\EntityDescriptor'))) {
                    $entity = new $className();
                    $result->add($entity->getEntityName(), $entity);
                }
            }
        }

        return $result;
    }

    /**
     * Returns the name of this module.
     *
     * @return string The name of this module
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the namespace of this module.
     *
     * @return string The namespace of this module
     */
    public function getNamespace()
    {
        if (false == isset($this->reflection)) {
            $this->reflection = new \ReflectionObject($this);
        }

        return $this->reflection->getNamespaceName();
    }

    /**
     * Returns the path of this module.
     *
     * @return string The path of this module
     */
    public function getPath()
    {
        if (false == isset($this->reflection)) {
            $this->reflection = new \ReflectionObject($this);
        }

        return dirname($this->reflection->getFileName());
    }

    /**
     * Get all routes this module provides and return them.
     *
     * @return RouteCollection A collection of all routes this module provides
     */
    public function getRoutes()
    {
        $result = new RouteCollection();
        $routeDir = $this->getPath().'/Routing';
        if (is_dir($routeDir)) {
            $routeFiles = glob($routeDir.'/*.Routing.php');
            foreach ($routeFiles as $fileName) {
                $tmpRoutes = require_once $fileName;
                if ((is_a($tmpRoutes, 'Bloomkit\Core\Routing\RouteCollection')) && ($tmpRoutes->getCount() > 0)) {
                    $result->addCollection($tmpRoutes);
                }
                unset($tmpRoutes);
            }
        }

        return $result;
    }

    /**
     * Lifecycle event. Every module is initialized during registration.
     * Application is already set here.
     */
    public function initialize()
    {
    }

    /**
     * Register all console commands of this module.
     */
    public function registerConsoleCommands()
    {
        $consoleDir = $this->getPath().'/Console';
        if (is_dir($consoleDir)) {
            $consoleFiles = glob($consoleDir.'/*Command.php');
            foreach ($consoleFiles as $fileName) {
                require_once $fileName;
                $baseName = basename($fileName, '.php');
                $className = $this->getNamespace().'\\Console\\'.ucfirst($baseName);
                if ((class_exists($className)) && (is_subclass_of($className, 'Bloomkit\Core\Console\ConsoleCommand'))) {
                    $consoleCommand = new $className($this->application);
                    $this->application->registerCommand($consoleCommand);
                }
            }
        }
    }

    /**
     * Register events this module should listen for on the EventManager.
     *
     * @param EventManager $eventManager EventManager to register events to
     */
    public function registerEvents(EventManager $eventManager)
    {
    }

    /**
     * Register further submodules of this module.
     * Called during registration.
     */
    public function registerSubmodules()
    {
    }

    /**
     * Set the Application object.
     *
     * @param Application $application The application object
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;
    }

    /**
     * If the shutdown-function of the application is called, this event is fired for all registered modules.
     */
    public function shutdown()
    {
    }
}
