<?php

namespace Sharp\Classes\CLI;

use Sharp\Core\Utils;

abstract class AbstractBuildTask
{
    protected bool $force = false;

    /**
     * Display messages in the console only if in command line context
     */
    public function log(...$strings)
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
    public function shellInDirectory(string $command, string $directory, bool $log=true)
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
     * This function tries to install vendor files in given directory
     */
    public function installVendorIn(string $directory)
    {
        $composer = Utils::joinPath($directory, 'composer.json');
        if (!is_file($composer))
            return;

        $vendorDir = Utils::joinPath($directory, 'vendor');

        if ($this->if(!is_dir($vendorDir)))
        {
            $this->log("Installing dependencies inside [$directory]\n");
            $this->shellInDirectory('composer install', $directory);
        }
    }

    /**
     * Call your function while being in a directory
     * Then go back to the previous directory
     */
    public function executeInDirectory(callable $function, string $directory)
    {
        $originalDirectory = getcwd();

        chdir($directory);
        $function();
        chdir($originalDirectory);
    }

    public function setForce(bool $force=true)
    {
        $this->force = $force;
    }

    /**
     * Can check any condition, will always be true if --force is present
     */
    public function if(bool $condition)
    {
        return $condition || $this->force;
    }

    /**
     * Main function of your build task, called every build
     */
    public function execute() { }
}