<?php

namespace Sharp\Commands\Configuration;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Classes\Env\Configuration;
use Sharp\Core\Autoloader;

class EnableCaches extends Command
{
    public function __invoke(Args $args)
    {
        $config = Configuration::getInstance();
        foreach (Autoloader::classesThatUses('Sharp\Classes\Core\Configurable') as $configurable)
        {
            /** @var Sharp\Classes\Core\Configurable $configurable */
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

    public function getHelp(): string
    {
        return "Enable every cachable components !";
    }
}