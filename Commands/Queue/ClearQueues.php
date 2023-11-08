<?php

namespace Sharp\Commands\Queue;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Classes\CLI\Terminal;
use Sharp\Classes\Extras\QueueHandler;
use Sharp\Core\Autoloader;

class ClearQueues extends Command
{
    public function getHelp(): string
    {
        return "Delete every files from your application queues";
    }

    public function __invoke(Args $args)
    {
        if (!Terminal::confirm("This action will delete every queue item in your application, process ?"))
            return;

        /** @var QueueHandler $class */
        foreach (Autoloader::classesThatUses(QueueHandler::class) as $class)
        {
            $storage = $class::getQueueStorage();

            foreach ($storage->listFiles() as $file)
            {
                $this->log("Deleting $file");
                unlink($file);
            }
        }
    }
}