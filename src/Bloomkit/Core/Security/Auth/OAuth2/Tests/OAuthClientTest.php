<?php

namespace Bloomkit\Core\Security\Auth\OAuth2\Tests;

use PHPUnit\Framework\TestCase;
use Bloomkit\Core\Security\Auth\OAuth2\OAuthClient;

class OAuthClientTest extends TestCase
{
    public function testSetClientId()
    {
        $client = new OAuthClient('12345');
        $this->assertEquals($client->getClientId(), '12345');
    }

    public function testSetUserId()
    {
        $client = new OAuthClient('12345');
        $client->setUserId('abcdef');
        $this->assertEquals($client->getUserId(), 'abcdef');
    }

    public function testSetTokenLifetime()
    {
        $client = new OAuthClient('12345');
        $this->assertEquals($client->getTokenLifetime(), -1);
        $client->setTokenLifetime('abcedf');
        $this->assertEquals($client->getTokenLifetime(), -1);
        $client->setTokenLifetime(-15);
        $this->assertEquals($client->getTokenLifetime(), -1);
        $client->setTokenLifetime(15);
        $this->assertEquals($client->getTokenLifetime(), 15);
    }

    public function testSetSecret()
    {
        $client = new OAuthClient('12345');
        $client->setUserId('secret');
        $this->assertEquals($client->getUserId(), 'secret');
    }

    public function testSetRedirectUris()
    {
        $uris = [
            'https://exampleA.com',
            'https://exampleB.com',
        ];
        $client = new OAuthClient('12345');
        $client->setRedirectUris($uris);
        $this->assertEquals($client->getRedirectUris(), $uris);
        $client->setRedirectUris([]);
        $this->assertEquals($client->getRedirectUris(), []);
    }
}
