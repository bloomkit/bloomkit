<?php

namespace Bloomkit\Core\Http\Tests;

use Bloomkit\Core\Http\HttpRequest;
use PHPUnit\Framework\TestCase;
use Bloomkit\Core\Http\Exceptions\SuspiciousOperationException;

class HttpRequestTest extends TestCase
{
    public function testCreateFromGlobals()
    {
        $normalizedMethod = 'GET';
        $_GET['foo1'] = 'bar1';
        $_POST['foo2'] = 'bar2';
        $_COOKIE['foo3'] = 'bar3';
        $_FILES['foo4'] = array('bar4');
        $_SERVER['foo5'] = 'bar5';

        $request = HttpRequest::processRequest();

        $this->assertEquals('bar1', $request->getGetParams()->get('foo1'));
        $this->assertEquals('bar2', $request->getPostParams()->get('foo2'));
        $this->assertEquals('bar3', $request->getCookies()->get('foo3'));
        $this->assertEquals(array('bar4'), $request->getFiles()->get('foo4'));
        $this->assertEquals('bar5', $request->getServerParams()->get('foo5'));
        $this->assertEquals('GET', $request->getHttpMethod());

        $methods = ['PUT', 'GET', 'DELETE', 'POST', 'PATCH'];
        foreach ($methods as $method) {
            $_SERVER['REQUEST_METHOD'] = $method;
            $request = HttpRequest::processRequest();
            $this->assertEquals($method, $request->getHttpMethod());
            unset($request);

            $_SERVER['REQUEST_METHOD'] = strtolower($method);
            $request = HttpRequest::processRequest();
            $this->assertEquals($method, $request->getHttpMethod());
            unset($request);
        }
    }

    public function testBaseUrl()
    {
        $_SERVER = [];
        $_SERVER['SCRIPT_FILENAME'] = '/foo/htdocs/bar/index.php';
        $_SERVER['SCRIPT_NAME'] = '/foobar/index.php';
        $_SERVER['REQUEST_URI'] = '/foobar/';
        $request = HttpRequest::processRequest();
        $this->assertEquals('/foobar', $request->getBaseUrl());
    }

    public function testPathUrl()
    {
        $_SERVER = [];
        $_SERVER['SCRIPT_FILENAME'] = '/foo/htdocs/bar/index.php';
        $_SERVER['SCRIPT_NAME'] = '/foobar/index.php';
        $_SERVER['REQUEST_URI'] = '/foobar/test?var=1';
        $request = HttpRequest::processRequest();
        $this->assertEquals('/test', $request->getPathUrl());
    }

    public function testGetClientIp()
    {
        $_SERVER = [];
        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';
        $request = HttpRequest::processRequest();
        $this->assertEquals('192.168.1.1', $request->getClientIp());
    }

    public function testGetHost()
    {
        $host = 'www.example.com';
        $_SERVER = [];
        $_SERVER['SERVER_NAME'] = $host;
        $request = HttpRequest::processRequest();
        $this->assertEquals($host, $request->getHost());

        $_SERVER = [];
        $_SERVER['SERVER_ADDR'] = $host;
        $request = HttpRequest::processRequest();
        $this->assertEquals($host, $request->getHost());
    }

    public function testGetHostWithForcedPort()
    {
        $_SERVER = [];
        $_SERVER['SERVER_NAME'] = 'www.example.com';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTPS'] = 'off';
        $request = HttpRequest::processRequest();
        $this->assertEquals('www.example.com:80', $request->getHost(true));
    }

    public function testGetHostWithPort()
    {
        $_SERVER = [];
        $_SERVER['SERVER_NAME'] = 'www.example.com';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTPS'] = 'off';
        $request = HttpRequest::processRequest();
        $this->assertEquals('www.example.com', $request->getHost());

        $_SERVER = [];
        $_SERVER['SERVER_NAME'] = 'www.example.com';
        $_SERVER['SERVER_PORT'] = '443';
        $_SERVER['HTTPS'] = 'on';
        $request = HttpRequest::processRequest();
        $this->assertEquals('www.example.com', $request->getHost());

        $_SERVER = [];
        $_SERVER['SERVER_NAME'] = 'www.example.com';
        $_SERVER['SERVER_PORT'] = '123';
        $_SERVER['HTTPS'] = 'off';
        $request = HttpRequest::processRequest();
        $this->assertEquals('www.example.com:123', $request->getHost());
    }

    public function testGetInvalidHost()
    {
        $this->expectException(SuspiciousOperationException::class);
        $_SERVER = [];
        $_SERVER['SERVER_NAME'] = 'www.exam/..ple.com';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTPS'] = 'off';
        $request = HttpRequest::processRequest();
        $request->getHost();
    }

    public function testGetPort()
    {
        $_SERVER = [];
        $_SERVER['SERVER_PORT'] = '123';
        $request = HttpRequest::processRequest();
        $this->assertEquals('123', $request->getPort());
    }

    public function testGetScheme()
    {
        $_SERVER = [];
        $_SERVER['SERVER_NAME'] = 'www.example.com';
        $_SERVER['SERVER_PORT'] = '443';
        $_SERVER['HTTPS'] = 'on';
        $request = HttpRequest::processRequest();
        $this->assertEquals('https', $request->getScheme());

        $_SERVER = [];
        $_SERVER['SERVER_NAME'] = 'www.example.com';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTPS'] = 'off';
        $request = HttpRequest::processRequest();
        $this->assertEquals('http', $request->getScheme());
    }

    public function testHeaders()
    {
        $_SERVER['HTTP_FOO1'] = 'bar1';
        $_SERVER['CONTENT_FOO2'] = 'bar2';
        $request = HttpRequest::processRequest();
        $this->assertEquals('bar1', $request->getHeaders()->get('FOO1'));
        $this->assertEquals('bar2', $request->getHeaders()->get('CONTENT_FOO2'));
    }

    public function testRequestUri()
    {
        $url = '/index.php/path/info?query=string';

        $_SERVER = [];
        $_SERVER['HTTP_X_ORIGINAL_URL'] = $url;
        $_SERVER['UNENCODED_URL'] = 'foo';
        $_SERVER['IIS_WasUrlRewritten'] = 'foo';
        $request = HttpRequest::processRequest();
        $this->assertEquals($url, $request->getRequestUri());
        $this->assertEquals(null, $request->getHeaders()->get('X_ORIGINAL_URL'));
        $this->assertEquals(null, $request->getServerParams()->get('HTTP_X_ORIGINAL_URL'));
        $this->assertEquals(null, $request->getServerParams()->get('UNENCODED_URL'));
        $this->assertEquals(null, $request->getServerParams()->get('IIS_WasUrlRewritten'));

        $_SERVER = [];
        $_SERVER['HTTP_X_REWRITE_URL'] = $url;
        $request = HttpRequest::processRequest();
        $this->assertEquals($url, $request->getRequestUri());
        $this->assertEquals(null, $request->getHeaders()->get('X_REWRITE_URL'));

        $_SERVER = [];
        $_SERVER['IIS_WasUrlRewritten'] = 1;
        $_SERVER['UNENCODED_URL'] = $url;
        $request = HttpRequest::processRequest();
        $this->assertEquals($url, $request->getRequestUri());
        $this->assertEquals(null, $request->getServerParams()->get('UNENCODED_URL'));
        $this->assertEquals(null, $request->getServerParams()->get('IIS_WasUrlRewritten'));

        $_SERVER = [];
        $_SERVER['ORIG_PATH_INFO'] = $url;
        $_SERVER['QUERY_STRING'] = 'foo=bar';
        $request = HttpRequest::processRequest();
        $this->assertEquals($url.'?foo=bar', $request->getRequestUri());
        $this->assertEquals(null, $request->getServerParams()->get('ORIG_PATH_INFO'));

        $_SERVER = [];
        $_SERVER['REQUEST_URI'] = $url;
        $request = HttpRequest::processRequest();
        $this->assertEquals($url, $request->getRequestUri());
    }
}
