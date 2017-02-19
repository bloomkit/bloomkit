<?php

namespace Bloomkit\Tests\Application;

use Bloomkit\Core\Application\Container;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
	public function testRules()
	{
		$container = new Container;
		$container->bind('Bloomkit\Tests\Container\IContainerContractStub', 'Bloomkit\Tests\Application\ContainerImplementationStub');
		$what1 = 'Bloomkit\Tests\Application\ContainerTestContextInjectOne';
		$what2 = 'Bloomkit\Tests\Application\ContainerTestContextInjectTwo';
		$needs = 'Bloomkit\Tests\Application\IContainerContractStub';
		$give1 = 'Bloomkit\Tests\Application\ContainerImplementationStub';
		$give2 = 'Bloomkit\Tests\Application\ContainerImplementationStubTwo';
        $container->addRule($what1, $needs, $give1);
        $container->addRule($what2, $needs, $give2);
        $one = $container->make($what1);
        $two = $container->make($what2);
        $this->assertInstanceOf($give1, $one->impl);
        $this->assertInstanceOf($give2, $two->impl);        
	}
	
	public function testResolve()
	{
	    $container = new Container;
	    $container->intValue = 15;
	    $container->strValue = 'test';
//	    $container->bind($abstract, $concrete);
	    $this->assertEquals($container->intValue, 15);
	    $this->assertEquals($container->strValue, 'test');
	}
	
	public function testAlias()
	{
	    $container = new Container;
	    $container->setAlias('aliasName', 'aliasValue');
	    $this->assertEquals($container->getAlias('aliasName'), 'aliasValue');
	}
}

interface IContainerContractStub
{
}

class ContainerImplementationStub implements IContainerContractStub
{
}

class ContainerImplementationStubTwo implements IContainerContractStub
{
}

class ContainerTestContextInjectOne
{
    public $impl;
    public function __construct(IContainerContractStub $impl)
    {
        $this->impl = $impl;
    }
}

class ContainerTestContextInjectTwo
{
    public $impl;
    public function __construct(IContainerContractStub $impl)
    {
        $this->impl = $impl;
    }
}