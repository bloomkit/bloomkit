<?php

namespace Bloomkit\Core\Console;

/**
 * ConsoleOutput class.
 */
class ConsoleOutput
{
    /**
     * ConsoleApplication.
     *
     * @var ConsoleApplication
     */
    private $application;

    /**
     * If true, every line is printed to stdout.
     *
     * @var bool
     */
    private $directOutput;

    /**
     * Output buffer.
     *
     * @var string
     */
    private $outputBuffer;

    /**
     * Constructor.
     *
     * @param ConsoleApplication $consoleApp   The console application object
     * @param bool               $directOutput If true, output is directly printed to stdout
     */
    public function __construct(ConsoleApplication $consoleApp, $directOutput = true)
    {
        $this->application = $consoleApp;
        $this->directOutput = $directOutput;
        $this->outputString = '';
    }

    /**
     * Clear the output buffer.
     */
    public function clear()
    {
        $this->outputBuffer = '';
    }

    /**
     * Return the output buffer.
     *
     * @return string The output buffer string
     */
    public function getOutputBuffer()
    {
        return str_replace(PHP_EOL, "\n", $this->outputBuffer);
    }

    /**
     * Write a line to output-buffer (if directOutput = true) or stdout.
     *
     * @param string $line Line to write
     */
    public function writeLine($line)
    {
        if (false == $this->directOutput) {
            $this->outputBuffer .= $line."\n";
        } else {
            echo $line;
        }
    }

    /**
     * Write output to file.
     *
     * @param string $path Path to output-file
     */
    public function writeOutputToFile($path)
    {
        $file = fopen($path, 'w');
        fwrite($file, $this->getOutputBuffer());
        fclose($file);
    }
}
