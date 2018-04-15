<?php

namespace Bloomkit\Core\Rest\Tests;

use Bloomkit\Core\Rest\RestRequest;
use PHPUnit\Framework\TestCase;
use Bloomkit\Core\Rest\Exceptions\RestException;

class RestRequestTest extends TestCase
{
    public function testCreateWithInvalidApiBase()
    {
        $this->expectException(RestException::class);
        $this->expectExceptionCode(31010);
        $_SERVER = [];
        $_SERVER['SCRIPT_FILENAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php/invalid/v1/test';
        $request = RestRequest::processRequest();
    }

    public function testCreateWithMissingParams1()
    {
        $this->expectException(RestException::class);
        $this->expectExceptionCode(31020);
        $_SERVER = [];
        $_SERVER['SCRIPT_FILENAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php/api/';
        $request = RestRequest::processRequest();
    }

    public function testCreateWithMissingParams2()
    {
        $this->expectException(RestException::class);
        $this->expectExceptionCode(31020);
        $_SERVER = [];
        $_SERVER['SCRIPT_FILENAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php/api/v1/';
        $request = RestRequest::processRequest();
    }

    public function testCreateWithCustomApiBase()
    {
        $_SERVER = [];
        $_SERVER['SCRIPT_FILENAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php/custom/v1/test';
        $request = new RestRequest($_SERVER, $_GET, '', $_COOKIE, $_FILES, 'custom');
    }

    public function testCreateFromGlobals()
    {
        $_SERVER = [];
        $_SERVER['SCRIPT_FILENAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php/api/v1/test';
        $request = RestRequest::processRequest();
    }

    public function testCreateWithVersionAndModule()
    {
        $_SERVER = [];
        $_SERVER['SCRIPT_FILENAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php/api/v1/test';
        $request = RestRequest::processRequest();
        $this->assertEquals($request->getApiVersion(), 'v1');
        $this->assertEquals($request->getPrefix(), '');
        $this->assertEquals($request->getModuleName(), 'test');
    }

    public function testCreateWithPrefix()
    {
        $_SERVER = [];
        $_SERVER['SCRIPT_FILENAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php/prefix/api/v1/test/foo/bar';
        $request = RestRequest::processRequest();
        $this->assertEquals($request->getApiVersion(), 'v1');
        $this->assertEquals($request->getPrefix(), 'prefix');
        $this->assertEquals($request->getModuleName(), 'test');
    }

    public function testCreateWithPostData()
    {
        $_SERVER = [];
        $_SERVER['SCRIPT_FILENAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php/custom/v1/test';

        $data = [];
        $data['name'] = 'John';
        $data['age'] = 30;
        $data['car'] = null;
        $dataStr = json_encode($data);
        $request = new RestRequest($_SERVER, $_GET, $dataStr, $_COOKIE, $_FILES, 'custom');
        $this->assertEquals($request->getPostData(), $dataStr);
        $this->assertEquals($request->getJsonData(), $data);
    }

    public function testCreateWithRestUrl()
    {
        $_SERVER = [];
        $_SERVER['SCRIPT_FILENAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php/prefix/api/v1/test/foo/bar?key=value';
        $request = RestRequest::processRequest();
        $this->assertEquals($request->getApiVersion(), 'v1');
        $this->assertEquals($request->getPrefix(), 'prefix');
        $this->assertEquals($request->getModuleName(), 'test');
        $this->assertEquals($request->getRestUrl(), '/test/foo/bar');
    }
}
