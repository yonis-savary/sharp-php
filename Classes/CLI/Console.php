<?php

namespace Sharp\Classes\CLI;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;
use Sharp\Classes\Core\Component;
use Sharp\Classes\Core\Events;
use Sharp\Core\Autoloader;

class Console
{
    use Component;

    /**
     * @return array<Command>
     */
    public function listCommands(): array
    {
        $classes = Autoloader::classesThatExtends(Command::class);
        $classes = array_map(fn($x) => new $x(), $classes);
        $classes = array_filter($classes, fn($x) => $x->getOrigin() != "tests");
        return $classes;
    }

    /**
     * @return array<Command>
     */
    public function findCommands(string $identifier): array
    {
        $classes = [];

        foreach ($this->listCommands() as $command)
        {
            if ( in_array($identifier, [
                $command->getName(),
                $command->getIdentifier()
            ]))
                $classes[] = $command;
        }

        return $classes;
    }

    public function printCommandList(): void
    {
        $commands = $this->listCommands();

        printf("(%s) commands availables :\n", count($commands));
        foreach ($commands as $command)
            printf(" - %s (%s)\n", $command->getName(), $command->getIdentifier());
    }

    public function handleArgv(array $argv): void
    {
        array_shift($argv); // Ignore script name !

        if (!count($argv))
        {
            print("A command name is needed !\n");
            $this->printCommandList();
            return;
        }

        $commandName = array_shift($argv);
        $commands = $this->findCommands($commandName);

        if (!count($commands))
        {
            print("No command with [$commandName] identifier found !\n");
            $this->printCommandList();
            return;
        }

        if (count($commands) > 1)
        {
            echo "Multiple commands for identifier [$commandName] found !\n";
            foreach ($commands as $command)
                echo " - " . $command->getIdentifier() . "\n";
            return;
        }

        $command = $commands[0];

        printf("%s[ %s ]%s\n", str_repeat("-", 5), $command->getIdentifier() , str_repeat("-", 25));
        $return = $command(Args::fromArray($argv));

        Events::getInstance()->dispatch("calledCommand", [
            "command" => $command::class,
            "name" => $command->getName(),
            "origin" => $command->getOrigin(),
            "identifier" => $command->getIdentifier(),
            "returned" => $return
        ]);
    }
}