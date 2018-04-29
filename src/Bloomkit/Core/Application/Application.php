<?php

namespace Bloomkit\Core\Application;

use Bloomkit\Core\Module\ModuleInterface;
use Bloomkit\Core\EventManager\EventTracerInterface;
use Bloomkit\Core\EventManager\Event;
use Psr\Logger\LoggerInterface;

class Application extends Container implements EventTracerInterface
{
    /**
     * @var string
     */
    protected $appName;

    /**
     * @var string
     */
    protected $appVersion;

    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $modules = [];

    /**
     * @var array
     */
    protected $services = [];

    /**
     * @var float
     */
    protected $startTime;

    /**
     * Constuctor.
     *
     * @param string $appName    The name of the application
     * @param string $appVersion The version of the application
     * @param string $basePath   The root directory of the application - the config dir should be in here
     * @param array  $config     An optional array with config values
     */
    public function __construct($appName = 'UNKNOWN', $appVersion = '0.0', $basePath = null, array $config = [])
    {
        $this->startTime = microtime(true);
        $this->appName = $appName;
        $this->appVersion = $appVersion;

        if (isset($basePath)) {
            if (false == is_dir($basePath)) {
                trigger_error("Invalid basePath: $basePath", E_USER_WARNING);
            } else {
                $this->basePath = rtrim($basePath, '\/');
            }
        }

        $this->bind('Bloomkit\Core\EventManager\EventTracerInterface', 'Bloomkit\Core\Application\Application', true);

        $this->register('app', $this);
        $this->registerFactory('config', 'Bloomkit\Core\Utilities\Repository', true);
        $this->registerFactory('eventManager', 'Bloomkit\Core\EventManager\EventManager', true);
        $this->registerFactory('securityContext', 'Bloomkit\Core\Security\SecurityContext', true);

        $this->setAlias('Bloomkit\Core\Application\Application', 'app');
        $this->setAlias('Bloomkit\Core\EventManager\EventManager', 'eventManager');

        $this->loadConfigFromFiles();
        if (count($config) > 0) {
            $this->getConfig()->addItems($config);
        }

        $this->registerFactory('logger', 'Bloomkit\Core\Application\DummyLogger', true);
    }

    /**
     * Returns the base path of the application.
     *
     * @return string|null
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Returns the path to the aggregated config file (cache).
     *
     * @return string
     */
    public function getCachedConfigPath()
    {
        return $this->basePath.'/cache/config.php';
    }

    /**
     * Returns the application configuration.
     *
     * @return \Bloomkit\Core\Utilities\Repository
     */
    public function getConfig()
    {
        return $this['config'];
    }

    /**
     * Returns a list of files in the config directory.
     *
     * @return array;
     */
    protected function getConfigFiles()
    {
        $files = [];
        $configPath = realpath($this->getConfigPath());

        if (!is_dir($configPath)) {
            return $files;
        }

        $iti = new \RecursiveDirectoryIterator($configPath);
        foreach (new \RecursiveIteratorIterator($iti) as $file) {
            if ('php' == $file->getExtension()) {
                $directory = dirname($file->getRealPath());

                //check if file is in a subfolder structure and bild a prefix
                $tree = trim(str_replace($configPath, '', $directory), DIRECTORY_SEPARATOR);
                if ($tree) {
                    $tree = str_replace(DIRECTORY_SEPARATOR, '.', $tree).'.';
                }

                $files[$tree.basename($file->getRealPath(), '.php')] = $file->getRealPath();
            }
        }

        return $files;
    }

    /**
     * Returns the path to the config directory.
     *
     * @return string
     */
    public function getConfigPath()
    {
        return $this->basePath.'/config';
    }

    /**
     * Returns the event manager.
     *
     * @return \Bloomkit\Core\EventManager\EventManager;
     */
    public function getEventManager()
    {
        return $this['eventManager'];
    }

    /**
     * Returns the application name + version as a string.
     *
     * @return string
     */
    public function getLongVersion()
    {
        return $this->appName.' '.$this->appVersion;
    }
    
    /**
     * Returns the logger.
     *
     * @return LoggerInterface;
     */
    public function getLogger()
    {
        return $this['logger'];
    }
    
    /**
     * Returns the security context.
     *
     * @return \Bloomkit\Core\Security\SecurityContext;
     */
    public function getSecurityContext()
    {
        return $this['securityContext'];
    }

    /**
     * Returns the start-timestamp of the application.
     *
     * @return float
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Check the config-dir for configuration files and load them into the config repo.
     */
    protected function loadConfigFromFiles()
    {
        $cachedConfig = $this->getCachedConfigPath();
        if (file_exists($cachedConfig)) {
            $items = require $cached;
            $this->getConfig()->addItems($items);
        } else {
            $configFiles = $this->getConfigFiles();
            foreach ($configFiles as $key => $path) {
                $this->getConfig()->set($key, require $path);
            }
        }
    }

    /**
     * Called from inside the eventManager after an event is triggered.
     *
     * @param string $eventName The name of the triggered event
     * @param Event  $event     The triggered event
     */
    public function onAfterEvent($eventName, Event $event)
    {
        if (!isset($this['tracer'])) {
            return;
        }

        $e = $event->getTracerEvent();
        if (isset($e) && ($e->isStarted())) {
            $e->stop();
        }
    }

    /**
     * Called from inside the eventManager before a listener is triggered.
     *
     * @param array  $listenerInfo The name of the triggered listener
     * @param string $eventName    The name of the triggered event
     * @param Event  $event        The triggered event
     */
    public function onAfterEventListener($listenerInfo, $eventName, Event $event)
    {
        if (!isset($this['tracer'])) {
            return;
        }

        $e = $event->getTracerListenerEvent();
        if (isset($e) && ($e->isStarted())) {
            $e->stop();
        }
    }

    /**
     * Called from inside the eventManager before an event is triggered.
     *
     * @param string $eventName The name of the triggered event
     * @param Event  $event     The triggered event
     */
    public function onBeforeEvent($eventName, Event $event)
    {
        if (!isset($this['tracer'])) {
            return;
        }

        $event->setTracerEvent($this->tracer->start($eventName, 'section'));
    }

    /**
     * Called from inside the eventManager after a listener is triggered.
     *
     * @param array  $listenerInfo The name of the triggered listener
     * @param string $eventName    The name of the triggered event
     * @param Event  $event        The triggered event
     */
    public function onBeforeEventListener($listenerInfo, $eventName, Event $event)
    {
        if (!isset($this['tracer'])) {
            return;
        }

        $event->setTracerEvent($this->tracer->start($eventName, 'event_listener'));
    }

    /**
     * Register an application module.
     *
     * @param Module $module
     */
    public function registerModule(ModuleInterface $module)
    {
        $this->modules[$module->getName()] = $module;
        $module->setApplication($this);
        $module->initialize();
        $module->registerEvents($this['eventManager']);
        $module->registerSubmodules();
    }

    /**
     * Register a ServiceProvider to the container.
     *
     * @param ServiceProviderInterface $service
     */
    public function registerService(ServiceProviderInterface $service)
    {
        $service->register();
        $this->services[] = $service;
    }

    /**
     * Start the application.
     */
    public function run()
    {
    }

    /**
     * Shutdown the application.
     */
    public function shutdown()
    {
        foreach ($this->modules as $module) {
            $module->shutdown();
        }
    }
}
