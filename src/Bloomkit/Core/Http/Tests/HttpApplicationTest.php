<?php

namespace Bloomkit\Core\Http\Tests;

use Bloomkit\Core\Http\HttpApplication;
use PHPUnit\Framework\TestCase;
use Bloomkit\Core\Http\HttpEvents;
use Bloomkit\Core\Http\HttpEvent;
use Bloomkit\Core\Http\HttpExceptionEvent;
use Bloomkit\Core\Http\HttpResponse;
use Bloomkit\Core\Http\Exceptions\HttpNotFoundException;
use Bloomkit\Core\Http\Session\Session;
use Bloomkit\Core\Http\Session\Storage\MockSessionStorage;

class HttpApplicationTest extends TestCase
{
    public function testUnhandledExceptionEvent()
    {
        $this->expectException(\Exception::class);
        $app = new HttpApplication();
        $app->session = new Session(new MockSessionStorage());
        $eventManager = $app->getEventManager();
        $eventManager->addListener(HttpEvents::REQUEST, array($this, 'onRequestTest1'));
        $app->run();
    }

    public function testHandledExceptionEvent()
    {
        $app = new HttpApplication();
        $app->session = new Session(new MockSessionStorage());
        $eventManager = $app->getEventManager();
        $eventManager->addListener(HttpEvents::REQUEST, array($this, 'onRequestTest1'));
        $eventManager->addListener(HttpEvents::EXCEPTION, array($this, 'onHttpException'));
        $response = $app->run(false);
        $this->assertEquals('foo', $response->getContent());
    }

    public function testNotFound()
    {
        $this->expectException(HttpNotFoundException::class);
        $_SERVER = [];
        $_SERVER['SCRIPT_FILENAME'] = '/htdocs/index.php';
        $_SERVER['SCRIPT_NAME'] = '/foo/index.php';
        $_SERVER['REQUEST_URI'] = '/foo/bar';
        $app = new HttpApplication();
        $app->session = new Session(new MockSessionStorage());
        $app->run(false);
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
