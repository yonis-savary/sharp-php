<?php

namespace Sharp\Classes\CLI;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Classes\Core\Component;
use Sharp\Core\Autoloader;

class Console
{
    use Component;

    public function listCommands()
    {
        $classes = Autoloader::classesThatExtends(Command::class);
        $commands = [];
        foreach ($classes as $class)
            $commands[] = new $class();
        return $commands;
    }

    public function findCommands(string $identifier)
    {
        $classes = [];
        foreach ($this->listCommands() as $command)
        {
            /** @var Command $command */

            if ( in_array($identifier, [
                $command->getName(),
                $command->getIdentifier()
            ]))
                $classes[] = $command;
        }
        return $classes;
    }

    public function printCommandList()
    {
        $commands = $this->listCommands();

        printf("(%s) commands availables :\n", count($commands));
        /** @var Command $command */
        foreach ($commands as $command)
            printf(" - %s (%s)\n", $command->getName(), $command->getIdentifier());
    }

    public function handleArgv(array $argv)
    {
        array_shift($argv); // Ignore script name !

        if (!count($argv))
        {
            print("A command name is needed !\n");
            return $this->printCommandList();
        }

        $commandName = array_shift($argv);
        $commands = $this->findCommands($commandName);

        if (!count($commands))
        {
            print("No command with [$commandName] identifier found !\n");
            return $this->printCommandList();
        }

        if (count($commands) === 1)
        {
            /** @var Command $command */
            $command = $commands[0];
            printf("%s[ %s ]%s\n", str_repeat("-", 5), $command->getIdentifier() , str_repeat("-", 25));
            return $command(Args::fromArray($argv));
        }

        echo "Multiple commands for identifier [$commandName] found !\n";
        /** @var Command $command */
        foreach ($commands as $command)
            echo " - " . $command->getIdentifier() . "\n";
    }
}