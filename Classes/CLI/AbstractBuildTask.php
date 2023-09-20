<?php

namespace Sharp\Classes\CLI;

abstract class AbstractBuildTask
{
    /**
     * Display messages in the console only if in command line context
     */
    final protected function log(...$strings): void
    {
        if (php_sapi_name() === 'cli')
            echo join("\n", $strings);
    }

    /**
     * Execute a shell command in a specified directory
     * @param string $command Command to execute
     * @param string $directory Target directory
     * @param bool $log If `true`, this command will display the command output
     */
    final protected function shellInDirectory(string $command, string $directory, bool $log=true): void
    {
        $this->executeInDirectory(function() use ($command, $log)
        {
            $proc = popen($command, 'r');
            if (!$log)
                return;

            while (!feof($proc))
                $this->log(fread($proc, 1024));

        }, $directory);
    }

    /**
     * Call your function while being in a directory
     * Then go back to the previous directory
     */
    final protected function executeInDirectory(callable $function, string $directory): void
    {
        $originalDirectory = getcwd();

        chdir($directory);
        $function();
        chdir($originalDirectory);
    }

    /**
     * Main function of your build task, called every build
     */
    public abstract function execute();
}