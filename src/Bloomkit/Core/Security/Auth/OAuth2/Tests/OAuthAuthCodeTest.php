<?php

namespace Bloomkit\Core\Security\Auth\OAuth2\Tests;

use PHPUnit\Framework\TestCase;
use Bloomkit\Core\Security\Auth\OAuth2\OAuthAuthCode;

class OAuthAuthCodeTest extends TestCase
{
    public function testConstructor()
    {
        $uris = [
            'https://exampleA.com',
            'https://exampleB.com',
        ];
        $code = new OAuthAuthCode('clientid', 'userid', 'token', time() + 10, 'scope', $uris);
        $this->assertEquals($code->getClientId(), 'clientid');
        $this->assertEquals($code->getUserId(), 'userid');
        $this->assertEquals($code->getToken(), 'token');
        $this->assertEquals($code->getExpiresIn(), 10);
        $this->assertEquals($code->getScope(), 'scope');
        $this->assertEquals($code->getRedirectUris(), $uris);
    }
}
