<?php
namespace Bloomkit\Core\Application;

class Application extends Container
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
     * @var float
     */
    protected $startTime;
    
    /**
     * @var array
     */
    protected $modules = [];

    /**
     * @var array
     */
    protected $services = [];
    
    /**
     * Constuctor
     *
     * @param string $appName       The name of the application  
     * @param string $appVersion    The version of the application
     * @param string $basePath      The root directory of the application - the config dir should be in here
     * @param array  $config        An optional array with config values
     */
    public function __construct($appName = 'UNKNOWN', $appVersion = '0.0', $basePath = null, $config = [])
    {
        $this->startTime = microtime(true);
        $this->appName = $appName;
        $this->appVersion = $appVersion;
    
        $this->register('app', $this);
        $this->registerFactory('config', 'Bloomkit\Core\Utilities\Repository', true);
    }
        
    /**
     * Returns the application configuration
     *
     * @return \Bloomkit\Core\Utilities\Repository
     */
    public function getConfig()
    {
        return $this['config'];
    }
    
    /**
     * Returns the start-timestamp of the application
     *
     * @return float
     */
    public function getStartTime()
    {
        return $this->startTime;
    }
        
    /**
     * Start the application
     */
    public function run()
    {
        //
    }
}