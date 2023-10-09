<?php

namespace Sharp\Commands;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Classes\CLI\Terminal;
use Sharp\Classes\Data\ModelGenerator\ModelGenerator;

class FetchModels extends Command
{
    public function __invoke(Args $args)
    {
        $app = Terminal::chooseApplication();
        $generator = ModelGenerator::getInstance();
        $generator->generateAll($app);
    }

    public function getHelp(): string
    {
        return "Create model classes from your database tables";
    }
}