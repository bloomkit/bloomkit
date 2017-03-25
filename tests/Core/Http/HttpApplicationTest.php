<?php

namespace Bloomkit\Tests\Application;

use Bloomkit\Core\Http\HttpApplication;
use PHPUnit\Framework\TestCase;

class HttpApplicationTest extends TestCase
{
    public function testApplication()
    {
        $app = new HttpApplication();
        $app->run();
    }
    
}
