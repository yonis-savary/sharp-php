<?php

namespace Sharp\Commands\Queue;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Core\Autoloader;
use Sharp\Classes\Extras\QueueHandler;

class ListQueues extends Command
{
    public function getHelp(): string
    {
        return "List items from your application queues, use --list to get a full list";
    }

    public function __invoke(Args $args)
    {
        $list = $args->isPresent("-l", "--list");

        $this->log("Listing application queues\n");

        /** @var QueueHandler $class */
        foreach (Autoloader::classesThatUses(QueueHandler::class) as $class)
        {
            $storage = $class::getQueueStorage();
            $files = $storage->listFiles();
            $this->log(sprintf("%s (%s items)", $class, count($files)));

            if (!$list)
                continue;

            foreach ($files as $file)
                echo " - $file\n";
        }

    }
}