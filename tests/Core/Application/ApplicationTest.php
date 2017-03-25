<?php

namespace Bloomkit\Tests\Application;

use Bloomkit\Core\Application\Application;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    public function testApplication()
    {
        $app = new Application();
        $app->run();
    }
    
    public function testConfig()
    {
        $app = new Application();
        $config = $app->getConfig();
        $this->assertInstanceOf('Bloomkit\Core\Utilities\Repository', $config);        
    }    
}
