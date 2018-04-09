<?php

namespace Bloomkit\Core\Console\Tests;

use Bloomkit\Core\Console\ConsoleArgument;

class ConsoleArgumentTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $argument = new ConsoleArgument('name');
        $this->assertEquals('name', $argument->getName());
        $argument = new ConsoleArgument('name', 'desc');
        $this->assertEquals('desc', $argument->getDescription());
    }

    public function testDefaultValue()
    {
        $argument = new ConsoleArgument('name', 'desc');
        $this->assertEquals(null, $argument->getDefault());
        $argument = new ConsoleArgument('name', 'desc', null);
        $this->assertEquals(null, $argument->getDefault());
        $argument = new ConsoleArgument('name', 'desc', 12345);
        $this->assertEquals(12345, $argument->getDefault());
        $argument = new ConsoleArgument('name', 'desc', 'default');
        $this->assertEquals('default', $argument->getDefault());
    }

    public function testIsRequired()
    {
        $argument = new ConsoleArgument('name', 'desc', null);
        $this->assertEquals(false, $argument->getIsRequired());
        $argument = new ConsoleArgument('name', 'desc', null, false);
        $this->assertEquals(false, $argument->getIsRequired());
        $argument = new ConsoleArgument('name', 'desc', null, true);
        $this->assertEquals(true, $argument->getIsRequired());
    }
}
