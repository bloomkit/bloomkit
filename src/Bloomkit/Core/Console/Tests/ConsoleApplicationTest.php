<?php

namespace Bloomkit\Core\Console\Tests;

use Bloomkit\Core\Console\ConsoleInput;
use Bloomkit\Core\Console\ConsoleOutput;
use Bloomkit\Core\Console\ConsoleCommand;
use Bloomkit\Core\Console\ConsoleApplication;

class ConsoleApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $consoleApp = new ConsoleApplication('name', '1.2.3.4');
        $this->assertEquals(['help', 'list'], array_keys($consoleApp->getCommandList()));
    }

    public function testGetCommandByName()
    {
        $consoleApp = new ConsoleApplication('name', '1.2.3.4');
        $this->assertInstanceOf('Bloomkit\Core\Console\HelpCommand', $consoleApp->getCommandByName('help'));
    }

    public function testGetHelpRequested()
    {
        ob_start();
        $consoleApp = new ConsoleApplication('name', '1.2.3.4');
        $_SERVER['argv'] = ['app.php', 'list', '--help'];
        $input = new ConsoleInput($consoleApp);
        $consoleApp->run($input);
        $this->assertEquals(true, $consoleApp->getHelpStatus());

        $consoleApp = new ConsoleApplication('name', '1.2.3.4');
        $_SERVER['argv'] = ['app.php', 'list', '-?'];
        $input = new ConsoleInput($consoleApp);
        $consoleApp->run($input);
        $this->assertEquals(true, $consoleApp->getHelpStatus());
        ob_end_clean();
    }

    public function testRegister()
    {
        $consoleApp = new ConsoleApplication('name', '1.2.3.4');
        $command = new ConsoleCommand($consoleApp, 'test1');
        $consoleApp->registerCommand($command);
        $this->assertEquals($command, $consoleApp->getCommandByName('test1'));
    }

    public function testGetCommandList()
    {
        $consoleApp = new ConsoleApplication('name', '1.2.3.4');
        $command = new ConsoleCommand($consoleApp, 'test1');
        $consoleApp->registerCommand($command);
        $this->assertEquals(['help', 'list', 'test1'], array_keys($consoleApp->getCommandList()));
    }

    public function testRun()
    {
        $fixturesPath = __DIR__.'/Fixtures/';
        $consoleApp = new ConsoleApplication('name', '1.2.3.4');
        $consoleApp->setScriptName('console.php');

        $_SERVER['argv'] = ['app.php', 'help'];
        $input = new ConsoleInput($consoleApp);
        $output = new ConsoleOutput($consoleApp, false);
        $consoleApp->run($input, $output);
        //The following line can be used to generate new output, if it may change sometime
        //$output->writeOutputToFile($fixturesPath.'run_output1.txt');
        $this->assertStringEqualsFile($fixturesPath.'run_output1.txt', $output->getOutputBuffer());

        $_SERVER['argv'] = ['app.php', 'list', '-?'];
        $input = new ConsoleInput($consoleApp);
        $output = new ConsoleOutput($consoleApp, false);
        $consoleApp->run($input, $output);
        $this->assertStringEqualsFile($fixturesPath.'run_output2.txt', $output->getOutputBuffer());

        $_SERVER['argv'] = ['app.php', 'list', '--help'];
        $input = new ConsoleInput($consoleApp);
        $output->clear();
        $consoleApp->run($input, $output);
        $this->assertStringEqualsFile($fixturesPath.'run_output2.txt', $output->getOutputBuffer());

        $_SERVER['argv'] = ['app.php', 'list'];
        $input = new ConsoleInput($consoleApp);
        $output->clear();
        $consoleApp->run($input, $output);
        $this->assertStringEqualsFile($fixturesPath.'run_output3.txt', $output->getOutputBuffer());
    }
}
