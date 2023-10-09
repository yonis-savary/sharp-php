<?php

namespace Sharp\Commands\Generators;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Classes\CLI\Terminal;
use Sharp\Classes\Env\Configuration;
use Sharp\Core\Utils;

class CreateApplication extends Command
{
    public function createApplication(string $appName)
    {
        if (!preg_match("/^(\/?[A-Z][a-zA-Z0-9]*)+$/", $appName))
            return print("Given app name must be made of PascalName words (can be separated by '/')\n");

        $appDirectory = Utils::relativePath($appName);

        if (is_dir($appDirectory))
            return print("[$appName] already exists\n");

        print("Making [$appName]\n");
        mkdir($appName, recursive:true);
    }

    public function __invoke(Args $args)
    {
        $values = $args->values();

        if (!count($values))
            $values = [Terminal::prompt("App name (PascalCase): ")];

        foreach($values as $app)
            $this->createApplication($app);

        print("Enabling new applications\n");

        $config = Configuration::getInstance();
        $config->edit("applications", function($applications=[]) use ($values) {
            array_push($applications, ...$values);
            return array_values(array_unique($applications));
        });
        $config->save();
    }

    public function getHelp(): string
    {
        return "Create an application directory and add it to your configuration";
    }
}