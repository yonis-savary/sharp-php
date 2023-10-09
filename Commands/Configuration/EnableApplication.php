<?php

namespace Sharp\Commands\Configuration;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Classes\CLI\Terminal;
use Sharp\Classes\Data\ObjectArray;
use Sharp\Classes\Env\Configuration;

class EnableApplication extends Command
{
    public function __invoke(Args $args)
    {
        $values = $args->values();

        if (!count($values))
            $values = [Terminal::prompt("App to enable (PascalCase): ")];

        $values = ObjectArray::fromArray($values);
        $values = $values->filter(function($app) {
            if (is_dir($app))
                return true;

            print("Skipping, [$app] is not a directory)\n");
            return false;
        });

        print("Enabling new applications\n");

        $config = Configuration::getInstance();
        $config->edit("applications", function($applications) use ($values) {
            array_push($applications, ...$values->collect());
            return array_values(array_unique($applications));
        });
        $config->save();
    }

    public function getHelp(): string
    {
        return "Enable applications by putting them in your configuration";
    }
}