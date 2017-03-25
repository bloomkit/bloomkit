<?php
namespace Bloomkit\Core\Http;

use Bloomkit\Core\Application\Application;

class HttpApplication extends Application
{

    /**
     * Constuctor
     *
     * @inheritdoc
     */
    public function __construct($appName = 'UNKNOWN', $appVersion = '0.0', $basePath = null, $config = array())
    {
        parent::__construct($appName, $appVersion, $basePath, $config);        
    }

}