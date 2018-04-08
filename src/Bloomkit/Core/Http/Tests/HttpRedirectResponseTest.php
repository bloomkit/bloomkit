<?php

namespace Bloomkit\Core\Http\Tests;

use Bloomkit\Core\Http\HttpRedirectResponse;
use PHPUnit\Framework\TestCase;

class HttpRedirectResponseTest extends TestCase
{
    public function testCreateResponse()
    {
        $response = new HttpRedirectResponse('https://www.example.com');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('https://www.example.com', $response->getTargetUrl());
    }

    public function testInvalidCreate()
    {
        $this->expectException(\InvalidArgumentException::class);
        $response = new HttpRedirectResponse('');
    }
}
