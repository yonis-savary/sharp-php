<?php

namespace Sharp\Commands;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Core\Autoloader;

class Help extends Command
{
    public function __invoke(Args $args)
    {
        $commands = Autoloader::classesThatExtends(Command::class);

        $maxLength = [
            "name" => 0,
            "identifier" => 0
        ];

        foreach ($commands as $class)
        {
            /** @var Command $command */
            $command = new $class();

            $maxLength["name"] = max($maxLength["name"], strlen($command->getName()));
            $maxLength["identifier"] = max($maxLength["identifier"], strlen($command->getIdentifier()));
        }

        echo "Availables commands with their identifier and purposes:\n";

        foreach ($commands as $class)
        {
            /** @var Command $command */
            $command = new $class();

            printf(" - %s %s : %s\n",
                str_pad($command->getName(), $maxLength["name"]),
                str_pad("(". $command->getIdentifier() .")", $maxLength["identifier"]+2),
                $command->getHelp()
            );
        }
    }
}