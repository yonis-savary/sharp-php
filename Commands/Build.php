<?php

namespace Sharp\Commands;

use Sharp\Classes\CLI\AbstractBuildTask;
use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Core\Autoloader;

class Build extends Command
{
    public function __invoke(Args $args)
    {
        echo "Building app...\n\n";

        /** @var AbstractBuildTask $class */
        foreach (Autoloader::classesThatExtends(AbstractBuildTask::class) as $class)
        {
            printf("Executing [%s]\n", $class);

            $task = new $class();
            $task->execute();

            echo "\n";
        }
    }
}