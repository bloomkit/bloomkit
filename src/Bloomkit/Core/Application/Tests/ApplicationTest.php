<?php

namespace Bloomkit\Core\Application\Tests;

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
        $app = new Application('foo', '1.0', __DIR__, array('foo' => 'bar'));
        $config = $app->getConfig();
        $this->assertInstanceOf('Bloomkit\Core\Utilities\Repository', $config);
        $this->assertEquals('bar', $config->get('foo'));
    }

    public function testGetLongVersion()
    {
        $app = new Application('foo', '1.0');
        $this->assertEquals('foo 1.0', $app->getLongVersion());
    }

    public function testInvalidPath()
    {
        $this->expectException(\PHPUnit_Framework_Error_Warning::class);
        $app = new Application('TestApp', '0.1', 'invalidPath');
        $app->run();
    }
}
