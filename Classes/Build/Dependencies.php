<?php

namespace Sharp\Classes\Build;

use Sharp\Classes\CLI\AbstractBuildTask;
use Sharp\Classes\Env\Config;
use Sharp\Core\Utils;

/**
 * This build task purpose is to install composer dependencies for every applications
 */
class Dependencies extends AbstractBuildTask
{
    protected function installDependenciesInApp(string $appName)
    {
        $appPath = Utils::relativePath($appName);

        if (!is_dir($appPath))
            return print("Cannot read [$appPath], inexistant directory");

        $composer = Utils::joinPath($appPath, "composer.json");
        $vendor = Utils::joinPath($appPath, "vendor");

        if (!is_file($composer))
            return print("Skipping [$appName] (no composer.json)\n");

        if (is_dir($vendor))
        {
            echo "Skipping [$appName] (Already installed)\n";
        }
        else
        {
            echo "Installing in [$appName]\n";
            echo "---\n";
            $this->shellInDirectory("composer install", $appPath);
            echo "---\n";
        }
    }

    public function execute()
    {
        echo "Installing dependencies...\n";
        $applications = Config::getInstance()->toArray("applications");
        array_unshift($applications, "Sharp", ...glob("Sharp/Extensions/*"));

        foreach ($applications as $appName)
            $this->installDependenciesInApp($appName);
    }
}