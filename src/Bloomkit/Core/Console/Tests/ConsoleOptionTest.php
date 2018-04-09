<?php

namespace Bloomkit\Core\Console\Tests;

use Bloomkit\Core\Console\ConsoleOption;

class ConsoleOptionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $name = 'optionName';
        $description = 'optionDesc';
        $shortcut = '-a';
        $requireValue = true;
        $default = 'default';
        $required = true;

        $option = new ConsoleOption($name, $description, $shortcut, $requireValue, $default, $required);
        $this->assertEquals($option->getShortcut(), 'a');
        $this->assertEquals($option->getName(), $name);
        $this->assertEquals($option->getDescription(), $desc);
        $this->assertEquals($option->getRequireValue(), true);
        $this->assertEquals($option->getDefault(), $default);
        $this->assertEquals($option->getIsRequired(), true);
    }
}
