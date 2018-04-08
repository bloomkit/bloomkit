<?php

namespace Bloomkit\Core\Auth\OAuth2\Tests;

use PHPUnit\Framework\TestCase;
use Bloomkit\Core\Auth\OAuth2\OAuthUtils;
use Bloomkit\Core\Auth\OAuth2\Exceptions\OAuthServerException;
use Bloomkit\Core\Auth\OAuth2\Exceptions\OAuthRedirectException;
use Bloomkit\Core\Http\HttpResponse;
use Bloomkit\Core\Http\HttpRedirectResponse;
use Bloomkit\Core\Http\HttpRequest;

class OAuthUtilsTest extends TestCase
{
    public function testBuildUrl()
    {
        $urlParts = [];
        $urlParts['scheme'] = 'https';
        $urlParts['user'] = 'user';
        $urlParts['pass'] = 'secret';
        $urlParts['host'] = 'www.example.com';
        $urlParts['port'] = 1234;
        $urlParts['path'] = '/path';
        $urlParts['query'] = 'key=value';
        $urlParts['fragment'] = 'fragment';
        $url = OAuthUtils::buildUrl($urlParts);
        $expected = 'https://user:secret@www.example.com:1234/path?key=value#fragment';
        $this->assertEquals($url, $expected);
    }

    public function testResponseForException()
    {
        $e = new \Exception('message', 123);
        $response = OAuthUtils::getResponseForException($e);

        $errorDoc = [
            'error' => 'message',
        ];
        $expectedContent = json_encode($errorDoc);
        $header = [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-store',
            'Pragma' => 'no-cache',
        ];
        $expectedHeader = json_encode($header);
        $this->assertTrue($response instanceof HttpResponse);
        $this->assertEquals($response->getStatusCode(), 400);
        $this->assertEquals($response->getContent(), $expectedContent);
        $this->assertEquals(json_encode($response->getHeaders()->getItems()), $expectedHeader);
    }

    public function testResponseForOAuthException()
    {
        $e = new OAuthServerException(400, 'message', 'desc', 'http://www.example.com', 'internal');
        $response = OAuthUtils::getResponseForException($e);

        $errorDoc = [
            'error' => 'message',
            'error_description' => 'desc',
            'error_uri' => 'http://www.example.com',
        ];
        $expectedContent = json_encode($errorDoc);
        $this->assertTrue($response instanceof HttpResponse);
        $this->assertEquals($response->getStatusCode(), 400);
        $this->assertEquals($response->getContent(), $expectedContent);
    }

    public function testResponseForOAuthRedirectException()
    {
        $redirectUri = 'https://www.example-b.com';
        $httpStatusCode = 302;
        $error = 'message';
        $state = '12345';
        $errorDescription = 'desc';
        $errorUri = 'http://www.example.com';
        $internalMessage = 'internal';
        $e = new OAuthRedirectException($redirectUri, $httpStatusCode, $error, $state, $errorDescription, $errorUri, $internalMessage);
        $response = OAuthUtils::getResponseForException($e);

        $fragment = 'error='.$error.'&error_description='.$errorDescription.'&error_uri='.urlencode($errorUri).'&state='.$state;
        $expected = 'https://www.example-b.com#'.$fragment;
        $this->assertTrue($response instanceof HttpRedirectResponse);
        $this->assertEquals($response->getStatusCode(), 302);
        $this->assertEquals($response->getTargetUrl(), $expected);
    }

    public function testGetBearerTokenFromRequest()
    {
        $token = '12345ABCDEfghij';
        $request = new HttpRequest();
        $request->getHeaders()->set('Authorization', 'Bearer '.$token);
        $resolvedToken = OAuthUtils::getBearerTokenFromRequest($request);
        $this->assertEquals($resolvedToken, $token);

        $request = new HttpRequest();
        $request->getHeaders()->set('HTTP_AUTHORIZATION', 'Bearer '.$token);
        $resolvedToken = OAuthUtils::getBearerTokenFromRequest($request);
        $this->assertEquals($resolvedToken, $token);
    }

    public function testGetResponseHeader()
    {
        $header = [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-store',
            'Pragma' => 'no-cache',
        ];
        $expectedHeader = json_encode($header);
        $this->assertEquals($expectedHeader, json_encode(OAuthUtils::getResponseHeader()));
    }
}
