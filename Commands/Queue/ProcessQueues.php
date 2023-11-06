<?php

namespace Sharp\Commands\Queue;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Core\Autoloader;
use Sharp\Classes\Extras\QueueHandler;

class ProcessQueues extends Command
{
    public function __invoke(Args $args)
    {
        $this->log("Processing application queues");

        /** @var QueueHandler $class */
        foreach (Autoloader::classesThatUses(QueueHandler::class) as $class)
        {
            $this->log("$class...");
            $class::processQueue();
        }
    }
}