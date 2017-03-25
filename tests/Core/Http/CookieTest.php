<?php

namespace Bloomkit\Tests\Http;

use Bloomkit\Core\Http\Cookie;
use PHPUnit\Framework\TestCase;

class CookieTest extends TestCase
{

    public function testEmptydName()
    {
        $this->expectException(\InvalidArgumentException::class);
        $cookie = new Cookie('');        
    }
    
    public function testInvalidName()
    {
        $this->expectException(\InvalidArgumentException::class);
        $cookie = new Cookie(';foo');
    }
    
    public function testInvalidExpire()
    {
        $this->expectException(\InvalidArgumentException::class);
        $cookie = new Cookie('name','value','foo');
    }
    
    public function testExpireDateTime()
    {
        $expire = new \DateTime();               
        $cookie = new Cookie('name','value',$expire);
        $this->assertEquals($cookie->getExpiresTime(), $expire->format('U'));
    }
    
    public function testExpireString()
    {
        $expire = '2017-03-25 00:00:00';
        $cookie = new Cookie('name','value',$expire);
        $this->assertEquals($cookie->getExpiresTime(), strtotime($expire));
    }
    
    public function testCreate()
    {
        $name       = 'name';
        $value      = 'value';
        $expire     = '2017-03-25 00:00:00';
        $path       = '/path';
        $domain     = 'www.example.com';
        $secureOnly = true;
        $httpOnly   = false;
        $raw        = true; 
        $sameSite   = 'strict';
        $cookie     = new Cookie($name, $value, $expire, $path, $domain, $secureOnly, $httpOnly, $raw, $sameSite);
        $this->assertEquals($cookie->getName(), $name);
        $this->assertEquals($cookie->getValue(), $value);
        $this->assertEquals($cookie->getPath(), $path);
        $this->assertEquals($cookie->getDomain(), $domain);
        $this->assertEquals($cookie->isSecureOnly(), $secureOnly);
        $this->assertEquals($cookie->isHttpOnly(), $httpOnly);
        $this->assertEquals($cookie->isRaw(), $raw);
        $this->assertEquals($cookie->getSameSite(), $sameSite);
        $this->assertEquals($cookie->getExpiresTime(), strtotime($expire));
    }
    
    public function testExpired()
    {
        $name       = 'name';
        $value      = 'value';
        $expire     = strtotime('+1 hour', time());
        $cookie     = new Cookie($name, $value, $expire);
        $this->assertEquals($cookie->isExpired(), false);
        
        $expire     = strtotime('-1 hour', time());
        $cookie     = new Cookie($name, $value, $expire);
        $this->assertEquals($cookie->isExpired(), true);
    }
}

