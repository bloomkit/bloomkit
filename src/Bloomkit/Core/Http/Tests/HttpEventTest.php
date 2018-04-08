<?php

namespace Bloomkit\Core\Http\Tests;

use Bloomkit\Core\Http\HttpRequest;
use PHPUnit\Framework\TestCase;
use Bloomkit\Core\Http\HttpEvent;
use Bloomkit\Core\Http\HttpResponse;

class HttpEventTest extends TestCase
{
    public function testGetRequest()
    {
        $request = HttpRequest::processRequest();
        $event = new HttpEvent($request);
        $this->assertEquals($request, $event->getRequest());
    }

    public function testSetResponse()
    {
        $request = HttpRequest::processRequest();
        $event = new HttpEvent($request);
        $this->assertEquals(false, $event->hasResponse());
        $this->assertEquals(false, $event->getStopProcessing());
        $response = new HttpResponse();
        $event->setResponse($response);
        $this->assertEquals(true, $event->hasResponse());
        $this->assertEquals(true, $event->getStopProcessing());
        $this->assertEquals($response, $event->getResponse());
    }
}
