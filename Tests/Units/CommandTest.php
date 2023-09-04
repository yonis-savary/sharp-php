<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Tests\Commands\DummyCommand;

class CommandTest extends TestCase
{
    protected function getDummyCommand(): Command
    {
        return new DummyCommand();
    }

    public function test_getOrigin()
    {
        $command = $this->getDummyCommand();
        $this->assertEquals("tests", $command->getOrigin());
    }

    public function test_getIdentifier()
    {
        $command = $this->getDummyCommand();
        $this->assertEquals("tests@dummy-command", $command->getIdentifier());
    }

    public function test_getName()
    {
        $command = $this->getDummyCommand();
        $this->assertEquals("dummy-command", $command->getName());
    }

    public function test_getHelp()
    {
        $command = $this->getDummyCommand();
        $this->assertEquals("Help", $command->getHelp());
    }

    public function test___invoke()
    {
        $command = $this->getDummyCommand();

        ob_start();
        $command(new Args());
        $output = ob_get_clean();

        $this->assertEquals("Hello", $output);
    }
}