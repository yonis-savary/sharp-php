<?php

namespace Sharp\Classes\CLI;

use Sharp\Classes\Core\Logger;

class CLIUtils
{
    /**
     * Display messages in the console only if in command line context
     */
    final public function log(string ...$mixed)
    {
        if (php_sapi_name() === "cli")
            return print(join("", array_map(fn($x) => $x . "\n", $mixed)));

        return Logger::getInstance()->info(...$mixed);
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
}