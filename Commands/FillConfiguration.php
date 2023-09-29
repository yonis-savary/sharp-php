<?php

namespace Sharp\Commands;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Classes\Env\Configuration;
use Sharp\Core\Autoloader;

class FillConfiguration extends Command
{
    public function __invoke(Args $args)
    {
        $configurables = Autoloader::classesThatUses('Sharp\Classes\Core\Configurable');

        $config = Configuration::getInstance();

        /**
         * @var Sharp\Classes\Core\Configurable $class
         */
        foreach ($configurables as $class)
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