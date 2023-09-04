<?php

namespace Sharp\Tests\Commands;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;

class DummyCommand extends Command
{
    public function __invoke(Args $args)
    {
        echo "Hello";
    }

    public function getHelp(): string
    {
        return "Help";
    }
}