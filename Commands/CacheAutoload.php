<?php

namespace Sharp\Commands;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Core\Autoloader;

class CacheAutoload extends Command
{
    public function __invoke(Args $args)
    {
        Autoloader::writeAutoloadCache();
        echo "File written : " . Autoloader::CACHE_FILE . "\n";
        echo "Delete it to switch to classic autoload\n";
    }

    public function getHelp(): string
    {
        return "Put your autoloader's data in cache for better performances";
    }
}