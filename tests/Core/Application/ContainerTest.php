<?php

namespace Bloomkit\Tests\Application;

use Bloomkit\Core\Application\Container;
use Bloomkit\Core\Application\Exception\DiInstantiationException;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testAssignValues()
    {
        $container = new Container;
        $container->foo1 = 'bar1';
        $container['foo2'] = 'bar2';
        $this->assertEquals('bar1', $container->foo1);
        $this->assertEquals('bar2', $container['foo2']);
    }
        
    public function testAccessInvalidKey()
    {
        $this->expectException(\InvalidArgumentException::class);
        $container = new Container;
        $value = $container->foo1;
    }
    
    public function testAssignClosures()
    {
        $container = new Container;
        
        //register explicit singleton factory by classname
        $container->registerFactory('foo1', 'Bloomkit\Tests\Application\ContainerTestClassOne', true);
        
        //register explicit factory by classname
        $container->registerFactory('foo2', 'Bloomkit\Tests\Application\ContainerTestClassOne', false);        
             
        //register implicit factory by classname
        $container->register('foo3', function () {
            return new ContainerTestClassOne();
        });        
        
        //Check if we have the right objects        
        $this->assertInstanceOf('Bloomkit\Tests\Application\ContainerTestClassOne', $container->foo1);        
        $this->assertInstanceOf('Bloomkit\Tests\Application\ContainerTestClassOne', $container->foo2);
        $this->assertInstanceOf('Bloomkit\Tests\Application\ContainerTestClassOne', $container->foo3);
        
        //Check if singleton is working
        $class1 = $container->foo1;
        $class2 = $container->foo1;
        $this->assertSame($class1, $class2);
        
        //Check if factory is working
        $class1 = $container->foo2;
        $class2 = $container->foo2;        
        $this->assertNotSame($class1, $class2);        
    }
    
    public function testDi()
    {
        $container = new Container;
        $className = 'Bloomkit\Tests\Application\ContainerTestClassTwo';
        $class = $container->make($className);
        $this->assertInstanceOf('Bloomkit\Tests\Application\ContainerTestClassTwo', $class);
        $this->assertInstanceOf('Bloomkit\Tests\Application\ContainerTestClassOne', $class->impl);
    }

    public function testDiWithUnresolvedBinding()
    {
        $this->expectException(DiInstantiationException::class);        
        $container = new Container;
        $className = 'Bloomkit\Tests\Application\ContainerTestClassThree';
        $class = $container->make($className);
    }
    
    public function testDiWithBinding()
    {
        $container = new Container;        
        $className = 'Bloomkit\Tests\Application\ContainerTestClassThree';
        $container->bind('Bloomkit\Tests\Application\ContainerTestInterface', 'Bloomkit\Tests\Application\ContainerTestClassFive');
        $class = $container->make($className);
    }
    
	public function testDiWithRules()
	{
		$container = new Container;
		$container->bind('Bloomkit\Tests\Application\ContainerTestInterface', 'Bloomkit\Tests\Application\ContainerTestClassFive');
		
		$what1 = 'Bloomkit\Tests\Application\ContainerTestClassThree';
		$what2 = 'Bloomkit\Tests\Application\ContainerTestClassFour';
		$needs = 'Bloomkit\Tests\Application\ContainerTestInterface';
		$give1 = 'Bloomkit\Tests\Application\ContainerTestClassFive';
		$give2 = 'Bloomkit\Tests\Application\ContainerTestClassSix';
		
        $container->addRule($what1, $needs, $give1);
        $container->addRule($what2, $needs, $give2);
        
        $one = $container->make($what1);
        $two = $container->make($what2);
        $this->assertInstanceOf($give1, $one->impl);
        $this->assertInstanceOf($give2, $two->impl);        
	}
		
	public function testAlias()
	{
	    $container = new Container;
	    $container->setAlias(ContainerTestClassOne::class, 'foo');
	    $this->assertEquals('foo', $container->getAlias(ContainerTestClassOne::class));
	}
}

class ContainerTestClassOne
{
}

class ContainerTestClassTwo
{
    public $impl;
    public function __construct(ContainerTestClassOne $impl)
    {
        $this->impl = $impl;
    }
}

class ContainerTestClassThree
{
    public $impl;
    public function __construct(ContainerTestInterface $impl)
    {
        $this->impl = $impl;
    }
}

class ContainerTestClassFour
{
    public $impl;
    public function __construct(ContainerTestInterface $impl)
    {
        $this->impl = $impl;
    }
}

class ContainerTestClassFive implements ContainerTestInterface
{
}

class ContainerTestClassSix implements ContainerTestInterface
{
}

interface ContainerTestInterface
{
}
