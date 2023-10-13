<?php

namespace Sharp\Commands\Configuration;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Classes\Env\Configuration;
use Sharp\Core\Autoloader;
use Sharp\Classes\Core\Configurable;

class FillConfiguration extends Command
{
    public function getHelp(): string
    {
        return "Complete or create your configuration with the framework's default configuration";
    }

    public function __invoke(Args $args)
    {
        $configurableList = Autoloader::classesThatUses(Configurable::class);
        $config = Configuration::getInstance();

        /**
         * @var Configurable $class
         */
        foreach ($configurableList as $class)
        {
            $configKey = $class::getConfigurationKey();

            $actual = $config->get($configKey, []);
            $default = $class::getDefaultConfiguration();

            $config->set($configKey, array_merge($default, $actual));

            $invalidKeys = array_diff(array_keys($actual), array_keys($default));

            echo "Merging $configKey configuration...\n";
            foreach ($invalidKeys as $key)
                echo " - Unsupported key [$key]\n";
        }

        $config->save();
    }
}