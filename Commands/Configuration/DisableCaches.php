<?php

namespace Sharp\Commands\Configuration;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Classes\Env\Configuration;
use Sharp\Core\Autoloader;
use Sharp\Classes\Core\Configurable;
use Sharp\Commands\ClearCaches;

class DisableCaches extends Command
{
    public function getHelp(): string
    {
        return "Disable every cache-able component (use -k to keep existants cache files)";
    }

    public function __invoke(Args $args)
    {
        if (!$args->isPresent("-k", "--keep-files"))
        {
            echo "Clearing all cache files...";
            ClearCaches::execute("--all");
        }

        $config = Configuration::getInstance();
        foreach (Autoloader::classesThatUses(Configurable::class) as $configurable)
        {
            /** @var Configurable $configurable */
            $key = $configurable::getConfigurationKey();

            if (!array_key_exists("cached", $configurable::getDefaultConfiguration()))
                continue;

            echo "Disabling [$key] cache\n";

            $config->edit($key, function($config){
                $config["cached"] = false;
                return $config;
            });
        }
        $config->save();
    }
}