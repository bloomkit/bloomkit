<?php

namespace Bloomkit\Core\Http\Tests;

use Bloomkit\Core\Http\HttpResponse;
use PHPUnit\Framework\TestCase;
use Bloomkit\Core\Http\Cookie;

class HttpResponseTest extends TestCase
{
    public function testCreateResponse()
    {
        $response = new HttpResponse('foo', 500);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('foo', $response->getContent());
    }
    
    public function testSendContent()
    {
        $response = new HttpResponse('foo', 200);
        ob_start();
        $response->sendContent();
        $string = ob_get_clean();
        $this->assertContains('foo', $string);
    }
    
    public function testSendHeader()
    {
        $cookie = new Cookie('foo', 'bar');
        $headers = array('Content-Type' => 'application/json');
        $cookies[] = $cookie;
        $response = new HttpResponse('', 200, $headers, $cookies);
        $response->sendHeaders();
    }
}
