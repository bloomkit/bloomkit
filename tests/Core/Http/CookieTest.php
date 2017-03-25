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
        $this->assertEquals($expire->format('U'), $cookie->getExpiresTime());
    }
    
    public function testExpireString()
    {
        $expire = '2017-03-25 00:00:00';
        $cookie = new Cookie('name','value',$expire);
        $this->assertEquals(strtotime($expire), $cookie->getExpiresTime());
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
        $this->assertEquals($name, $cookie->getName());
        $this->assertEquals($value, $cookie->getValue());
        $this->assertEquals($path, $cookie->getPath());
        $this->assertEquals($domain, $cookie->getDomain());
        $this->assertEquals($secureOnly, $cookie->isSecureOnly());
        $this->assertEquals($httpOnly, $cookie->isHttpOnly());
        $this->assertEquals($raw, $cookie->isRaw());
        $this->assertEquals($sameSite, $cookie->getSameSite());
        $this->assertEquals(strtotime($expire), $cookie->getExpiresTime());
    }
    
    public function testExpired()
    {
        $name       = 'name';
        $value      = 'value';
        $expire     = strtotime('+1 hour', time());
        $cookie     = new Cookie($name, $value, $expire);
        $this->assertEquals(false, $cookie->isExpired());
        
        $expire     = strtotime('-1 hour', time());
        $cookie     = new Cookie($name, $value, $expire);
        $this->assertEquals(true, $cookie->isExpired());
    }
}

