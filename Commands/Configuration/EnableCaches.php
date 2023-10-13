<?php

namespace Sharp\Commands\Configuration;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Classes\Env\Configuration;
use Sharp\Core\Autoloader;
use Sharp\Classes\Core\Configurable;

class EnableCaches extends Command
{
    public function getHelp(): string
    {
        return "Enable every cache-able components !";
    }

    public function __invoke(Args $args)
    {
        $config = Configuration::getInstance();
        foreach (Autoloader::classesThatUses(Configurable::class) as $configurable)
        {
            /** @var Configurable $configurable */
            $key = $configurable::getConfigurationKey();

            if (!array_key_exists("cached", $configurable::getDefaultConfiguration()))
                continue;

            echo "Enabling [$key] cache\n";

            $config->edit($key, function($config){
                $config["cached"] = true;
                return $config;
            });
        }
        $config->save();
    }
}