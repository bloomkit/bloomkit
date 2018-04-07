<?php

namespace Bloomkit\Core\Http\Tests;

use Bloomkit\Core\Http\HttpApplication;
use PHPUnit\Framework\TestCase;
use Bloomkit\Core\EventManager\EventManager;
use Bloomkit\Core\Http\HttpEvents;
use Bloomkit\Core\Http\HttpEvent;
use Bloomkit\Core\Http\HttpExceptionEvent;
use Bloomkit\Core\Http\HttpResponse;
use Bloomkit\Core\Routing\Exceptions\RessourceNotFoundException;

class HttpApplicationTest extends TestCase
{    
    public function testUnhandledExceptionEvent()
    {
        $this->expectException(\Exception::class);
        $app = new HttpApplication();
        $eventManager = $app->getEventManager();
        $eventManager->addListener(HttpEvents::REQUEST, array($this, 'onRequestTest1'));
        $app->run();        
    }
    
    public function testHandledExceptionEvent()
    {
        $app = new HttpApplication();
        $eventManager = $app->getEventManager();
        $eventManager->addListener(HttpEvents::REQUEST, array($this, 'onRequestTest1'));
        $eventManager->addListener(HttpEvents::EXCEPTION, array($this, 'onHttpException'));
        $response = $app->run();
        $this->assertEquals('foo',$response->getContent()); 
    }
    
    public function testNotFound()
    {
        $this->expectException(RessourceNotFoundException::class);
        $_SERVER = [];
        $_SERVER['SCRIPT_FILENAME'] = '/htdocs/index.php';
        $_SERVER['SCRIPT_NAME']     = '/foo/index.php';
        $_SERVER['REQUEST_URI']     = '/foo/bar';
        $app = new HttpApplication();
        $app->run();
    }
    
    public function onRequestTest1(HttpEvent $event)
    {
        throw new \Exception('foo');
    }
    
    public function onHttpException(HttpExceptionEvent $event)
    {
        $response = new HttpResponse('foo');
        $event->setResponse($response);
    }
    
}
