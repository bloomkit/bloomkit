<?php

namespace Bloomkit\Core\Security\OAuth2\Tests;

use PHPUnit\Framework\TestCase;
use Bloomkit\Core\Http\HttpRequest;
use Bloomkit\Core\Http\HttpResponse;
use Bloomkit\Core\Http\HttpRedirectResponse;
use Bloomkit\Core\Security\OAuth2\OAuthServer;
use Bloomkit\Core\Security\OAuth2\Storage\OAuthStorageInterface;
use Bloomkit\Core\Security\OAuth2\Exceptions\OAuthServerException;
use Bloomkit\Core\Security\OAuth2\Tests\Mockups\MockOAuthStorage;
use Bloomkit\Core\Security\OAuth2\Tests\Mockups\MockUser;

class OAuthServerTest extends TestCase
{
    /**
     * @var OAuthServer
     */
    private $authServer;

    /**
     * @var OAuthStorageInterface
     */
    private $storage;

    public function setUp()
    {
        $this->storage = new MockOAuthStorage();
        $this->authServer = new OAuthServer($this->storage);
        parent::setUp();
    }

    public function testGrantWithMissingClientId()
    {
        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage('invalid_request');
        $request = new HttpRequest();
        $request->getServerParams()->set('HTTPS', 'on');
        $request->getGetParams()->set('response_type', 'abc');
        $user = new MockUser();
        $this->authServer->authorize($request, $user);
    }

    public function testGrantWithMissingResponseType()
    {
        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage('invalid_request');
        $request = new HttpRequest();
        $request->getServerParams()->set('HTTPS', 'on');
        $request->getGetParams()->set('client_id', 'abc');
        $user = new MockUser();
        $this->authServer->authorize($request, $user);
    }

    public function testGrantWithInvalidResponseType()
    {
        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage('unsupported_response_type');
        $request = new HttpRequest();
        $request->getServerParams()->set('HTTPS', 'on');
        $request->getGetParams()->set('response_type', 'abc');
        $request->getGetParams()->set('client_id', 'abc');
        $user = new MockUser();
        $this->authServer->authorize($request, $user);
    }

    public function testGrantWithInvalidClient()
    {
        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage('unauthorized_client');
        $request = new HttpRequest();
        $request->getServerParams()->set('HTTPS', 'on');
        $request->getGetParams()->set('response_type', 'code');
        $request->getGetParams()->set('client_id', 'invalid');
        $user = new MockUser();
        $this->authServer->authorize($request, $user);
    }

    public function testGrantWithForcedError()
    {
        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage('server_error');
        $request = new HttpRequest();
        $request->getServerParams()->set('HTTPS', 'on');
        $request->getGetParams()->set('response_type', 'code');
        $request->getGetParams()->set('client_id', 'exception');
        $user = new MockUser();
        $this->authServer->authorize($request, $user);
    }

    public function testGrantWithInvalidRedirectUri()
    {
        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage('access_denied');
        $request = new HttpRequest();
        $request->getServerParams()->set('HTTPS', 'on');
        $request->getGetParams()->set('response_type', 'code');
        $request->getGetParams()->set('client_id', 'valid');
        $request->getGetParams()->set('redirect_uri', 'https://wrongsite.com');
        $user = new MockUser();
        $this->authServer->authorize($request, $user);
    }

    public function testGrantWithInvalidClientUser()
    {
        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage('access_denied');
        $request = new HttpRequest();
        $request->getServerParams()->set('HTTPS', 'on');
        $request->getGetParams()->set('response_type', 'code');
        $request->getGetParams()->set('client_id', 'valid-with-user');
        $request->getGetParams()->set('redirect_uri', 'https://www.example.com');
        $user = new MockUser();
        $user->setUserId('invalidUser');
        $this->authServer->authorize($request, $user);
    }

    public function testTokenGeneration()
    {
        $reflection = new \ReflectionClass($this->authServer);
        $method = $reflection->getMethod('createTokenCode');
        $method->setAccessible(true);
        $token = $method->invoke($this->authServer);
        $this->assertEquals(86, strlen($token));
    }

    public function testGrant()
    {
        $redirectUri = 'https://www.example.com';
        $scope = 'myscope';
        $state = 'mystate';
        $request = new HttpRequest();
        $request->getServerParams()->set('HTTPS', 'on');
        $request->getGetParams()->set('response_type', 'code');
        $request->getGetParams()->set('client_id', 'valid-with-user');
        $request->getGetParams()->set('redirect_uri', $redirectUri);
        $request->getGetParams()->set('scope', $scope);
        $request->getGetParams()->set('state', $state);
        $user = new MockUser();
        $user->setUserId('alloweduser');
        $response = $this->authServer->authorize($request, $user);

        $authCode = $this->storage->authCode;
        $client = $this->storage->client;
        $this->assertTrue(is_array($authCode));
        $this->assertEquals($authCode['user'], $user);
        $this->assertEquals($authCode['client'], $client);
        $this->assertEquals(86, strlen($authCode['authCode']));
        $this->assertEquals($authCode['redirectUri'], $redirectUri);
        $this->assertEquals($authCode['scope'], $scope);
        $this->assertTrue(time() - $authCode['expire'] < 10);
        $this->assertTrue($response instanceof HttpRedirectResponse);
        $expRedirUrl = 'https://www.example.com?code='.$authCode['authCode'].'&state='.$state;
        $this->assertEquals($response->getTargetUrl(), $expRedirUrl);
    }

    public function testImplicitGrant()
    {
        $redirectUri = 'https://www.example.com';
        $scope = 'myscope';
        $state = 'mystate';
        $request = new HttpRequest();
        $request->getServerParams()->set('HTTPS', 'on');
        $request->getGetParams()->set('response_type', 'token');
        $request->getGetParams()->set('client_id', 'valid-with-user');
        $request->getGetParams()->set('redirect_uri', $redirectUri);
        $request->getGetParams()->set('scope', $scope);
        $request->getGetParams()->set('state', $state);
        $user = new MockUser();
        $user->setUserId('alloweduser');
        $response = $this->authServer->authorize($request, $user);

        $accessToken = $this->storage->accessToken;
        $client = $this->storage->client;
        $this->assertTrue(is_array($accessToken));
        $this->assertEquals($accessToken['user'], $user);
        $this->assertEquals($accessToken['client'], $client);
        $this->assertEquals(86, strlen($accessToken['accessToken']));
        $this->assertEquals($accessToken['scope'], $scope);
        $this->assertTrue((time() + 3600) - $accessToken['expire'] < 10);
        $this->assertTrue($response instanceof HttpRedirectResponse);
        $expRedirUrl = 'https://www.example.com#access_token='.$accessToken['accessToken'].'&token_type=bearer&expires_in=3600'.'&scope='.$scope.'&state='.$state;
        $this->assertEquals($response->getTargetUrl(), $expRedirUrl);
    }

    public function testGetTokenWithInvalidGrantType()
    {
        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage('invalid_request');
        $request = new HttpRequest();
        $request->getServerParams()->set('HTTPS', 'on');
        $request->getPostParams()->set('grant_type', 'invalid');
        $response = $this->authServer->requestToken($request);
    }

    public function testGetTokenWithoutSsl()
    {
        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage('invalid_request');
        $token = 'token';
        $request = new HttpRequest();
        $request->getPostParams()->set('grant_type', 'authorization_code');
        $request->getPostParams()->set('code', $token);
        $response = $this->authServer->requestToken($request);
    }

    public function testGetTokenWithForcedError()
    {
        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage('server_error');
        $token = 'token';
        $request = new HttpRequest();
        $request->getServerParams()->set('HTTPS', 'on');
        $request->getPostParams()->set('grant_type', 'authorization_code');
        $request->getPostParams()->set('code', $token);
        $request->getPostParams()->set('client_id', 'exception');
        $this->authServer->requestToken($request);
    }

    public function testGetTokenWithInvalidClientSecret()
    {
        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage('access_denied');
        $token = 'token';
        $request = new HttpRequest();
        $request->getServerParams()->set('HTTPS', 'on');
        $request->getPostParams()->set('grant_type', 'authorization_code');
        $request->getPostParams()->set('code', $token);
        $request->getPostParams()->set('client_id', 'valid-with-secret');
        $this->authServer->requestToken($request);
    }

    public function testGetTokenWithHttpAuth()
    {
        $token = 'valid-with-secret';
        $scope = 'ignoredscope';
        $request = new HttpRequest();
        $request->getServerParams()->set('HTTPS', 'on');
        $request->getPostParams()->set('grant_type', 'refresh_token');
        $request->getPostParams()->set('refresh_token', $token);
        $request->getPostParams()->set('client_id', 'valid-with-secret');
        $request->getPostParams()->set('scope', $scope);
        $request->getServerParams()->set('PHP_AUTH_PW', 'secret');
        $response = $this->authServer->requestToken($request);
        self::assertTrue(true);
    }

    public function testGetTokenWithParamAuth()
    {
        $token = 'valid-with-secret';
        $scope = 'ignoredscope';
        $request = new HttpRequest();
        $request->getServerParams()->set('HTTPS', 'on');
        $request->getPostParams()->set('grant_type', 'refresh_token');
        $request->getPostParams()->set('refresh_token', $token);
        $request->getPostParams()->set('client_id', 'valid-with-secret');
        $request->getPostParams()->set('client_secret', 'secret');
        $request->getPostParams()->set('scope', $scope);
        $response = $this->authServer->requestToken($request);
        self::assertTrue(true);
    }

    public function testGetRefreshTokenWithInvalidCode()
    {
        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage('invalid_token');
        $token = 'invalid';
        $request = new HttpRequest();
        $request->getServerParams()->set('HTTPS', 'on');
        $request->getPostParams()->set('grant_type', 'refresh_token');
        $request->getPostParams()->set('refresh_token', $token);
        $request->getPostParams()->set('client_id', 'valid');
        $response = $this->authServer->requestToken($request);
    }

    public function testGetRefreshTokenWithExpiredCode()
    {
        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage('invalid_token');
        $token = 'expired';
        $request = new HttpRequest();
        $request->getServerParams()->set('HTTPS', 'on');
        $request->getPostParams()->set('grant_type', 'refresh_token');
        $request->getPostParams()->set('refresh_token', $token);
        $request->getPostParams()->set('client_id', 'valid');
        $response = $this->authServer->requestToken($request);
    }

    public function testGetRefreshTokenWithForcedError()
    {
        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage('server_error');
        $token = 'exception';
        $request = new HttpRequest();
        $request->getServerParams()->set('HTTPS', 'on');
        $request->getPostParams()->set('grant_type', 'refresh_token');
        $request->getPostParams()->set('refresh_token', $token);
        $request->getPostParams()->set('client_id', 'valid');
        $response = $this->authServer->requestToken($request);
    }

    public function testGetRefreshToken()
    {
        $token = 'valid';
        $scope = 'ignoredscope';
        $request = new HttpRequest();
        $request->getServerParams()->set('HTTPS', 'on');
        $request->getPostParams()->set('grant_type', 'refresh_token');
        $request->getPostParams()->set('refresh_token', $token);
        $request->getPostParams()->set('client_id', 'valid');
        $request->getPostParams()->set('scope', $scope);
        $response = $this->authServer->requestToken($request);

        $accessToken = $this->storage->accessToken;
        $client = $this->storage->client;
        $this->assertTrue(is_array($accessToken));
        $this->assertEquals($accessToken['client'], $client);
        $this->assertEquals(86, strlen($accessToken['accessToken']));
        $this->assertEquals($accessToken['scope'], 'myscope');
        $this->assertTrue((time() + 3600) - $accessToken['expire'] < 10);
        $this->assertTrue($response instanceof HttpResponse);

        $expectedToken = json_encode(array(
            'access_token' => $accessToken['accessToken'],
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ));

        $expectedHeader = json_encode(array(
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-store',
            'Pragma' => 'no-cache',
        ));

        $this->assertEquals($response->getContent(), $expectedToken);
        $this->assertEquals(json_encode($response->getHeaders()->getItems()), $expectedHeader);
    }
}
