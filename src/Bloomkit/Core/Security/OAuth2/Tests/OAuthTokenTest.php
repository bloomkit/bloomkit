<?php

namespace Bloomkit\Core\Security\OAuth2\Tests;

use PHPUnit\Framework\TestCase;
use Bloomkit\Core\Security\OAuth2\OAuthToken;

class OAuthTokenTest extends TestCase
{
    public function testConstructor()
    {
        $token = new OAuthToken('clientid', 'userid', 'token', time() + 10, 'scope');
        $this->assertEquals($token->getClientId(), 'clientid');
        $this->assertEquals($token->getUserId(), 'userid');
        $this->assertEquals($token->getToken(), 'token');
        $this->assertEquals($token->getExpiresIn(), 10);
        $this->assertEquals($token->getScope(), 'scope');
    }

    public function testSetToken()
    {
        $token = new OAuthToken('', '', '');
        $token->setToken('token');
        $this->assertEquals($token->getToken(), 'token');
    }

    public function testSetClientId()
    {
        $token = new OAuthToken('', '', '');
        $token->setClientId('clientid');
        $this->assertEquals($token->getClientId(), 'clientid');
    }

    public function testSetUserId()
    {
        $token = new OAuthToken('', '', '');
        $token->setUserId('userid');
        $this->assertEquals($token->getUserId(), 'userid');
    }

    public function testSetScope()
    {
        $token = new OAuthToken('', '', '');
        $token->setScope('scope');
        $this->assertEquals($token->getScope(), 'scope');
    }

    public function testSetExpires()
    {
        $token = new OAuthToken('', '', '');
        $token->setExpiresAt(time() + 10);
        $this->assertEquals($token->getExpiresIn(), 10);
        $this->assertEquals($token->hasExpired(), false);
        $token->setExpiresAt(time() - 10);
        $this->assertEquals($token->getExpiresIn(), 0);
        $this->assertEquals($token->hasExpired(), true);
        $token->setExpiresAt(null);
        $this->assertEquals($token->getExpiresIn(), PHP_INT_MAX);
        $this->assertEquals($token->hasExpired(), false);
    }
}
