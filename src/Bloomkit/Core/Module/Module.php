<?php

namespace Bloomkit\Core\Module;

use Bloomkit\Core\Utilities\Repository;
use Bloomkit\Core\Application\Application;
use Bloomkit\Core\EventManager\EventManager;
use Bloomkit\Core\Routing\RouteCollection;

/**
 * Representation of a bloomkit module.
 */
abstract class Module implements ModuleInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $options = [];

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
    public function __construct($moduleName, $options = [])
    {
        $this->options['registerRoutes'] = true;
        $this->options['registerEntites'] = true;
        $this->options['registerConsole'] = true;
        $this->options = array_merge($this->options, $options);
        $this->name = $moduleName;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntities()
    {
        $result = new Repository();
        if ((isset($this->options['registerEntities'])) && ($this->options['registerEntities'] === false)) {
            return $result;
        }

        $entityDir = $this->getPath().'/Entities';
        if (is_dir($entityDir)) {
            $entityFiles = glob($entityDir.'/*Entity.php');

            foreach ($entityFiles as $fileName) {
                require_once $fileName;
                $baseName = basename($fileName, '.php');
                $className = $this->getNamespace().'\\Entities\\'.ucfirst($baseName);
                if ((class_exists($className)) && (is_subclass_of($className, 'Bloomkit\Core\Entities\Descriptor\EntityDescriptor'))) {
                    $entity = new $className();
                    $result->set($entity->getEntityName(), $entity);
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        if (false == isset($this->reflection)) {
            $this->reflection = new \ReflectionObject($this);
        }

        return $this->reflection->getNamespaceName();
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        if (false == isset($this->reflection)) {
            $this->reflection = new \ReflectionObject($this);
        }

        return dirname($this->reflection->getFileName());
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes()
    {
        $result = new RouteCollection();
        if ((isset($this->options['registerRoutes'])) && ($this->options['registerRoutes'] === false)) {
            return $result;
        }

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
     * {@inheritdoc}
     */
    public function initialize()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function onModulesLoaded()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function registerConsoleCommands()
    {
        if ((isset($this->options['registerConsole'])) && ($this->options['registerConsole'] === false)) {
            return;
        }

        $consoleDir = $this->getPath().'/Console';
        if (is_dir($consoleDir)) {
            $consoleFiles = glob($consoleDir.'/*Command.php');
            foreach ($consoleFiles as $fileName) {
                require_once $fileName;
                $baseName = basename($fileName, '.php');
                $className = $this->getNamespace().'\\Console\\'.ucfirst($baseName);
                if ((class_exists($className)) && (is_subclass_of($className, 'Bloomkit\Core\Console\ConsoleCommand'))) {
                    $consoleCommand = new $className($this->application);
                    if (!$consoleCommand->isDisabled()) {
                        $this->application->registerCommand($consoleCommand);
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function registerEvents(EventManager $eventManager)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function registerSubmodules()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;
    }

    /**
     * {@inheritdoc}
     */
    public function shutdown()
    {
    }
}
