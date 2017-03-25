<?php

namespace Bloomkit\Tests\Http;

use Bloomkit\Core\Http\HttpResponse;
use PHPUnit\Framework\TestCase;
use Bloomkit\Core\Utilities\Repository;
use Bloomkit\Core\Http\Cookie;

class HttpResponseTest extends TestCase
{
	
	public function testCreateResponse()
	{
		$response = HttpResponse::createResponse(500, 'foo');
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
		$cookie  	= new Cookie('foo','bar');
		$headers	= array('Content-Type' => 'application/json');
		$cookies[] 	= $cookie;;
		$response 	= new HttpResponse('', 200, $headers, $cookies);				
		$response->sendHeaders();		
	}	
	
}