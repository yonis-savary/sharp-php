<?php

namespace Sharp\Classes\Build;

use Sharp\Classes\CLI\AbstractBuildTask;
use Sharp\Classes\Env\Configuration;
use Sharp\Classes\Env\Storage;
use Sharp\Core\Utils;

/**
 * This build task purpose is to install composer dependencies for every applications
 */
class InstallDependencies extends AbstractBuildTask
{
    public function execute()
    {
        echo "Installing dependencies...\n";

        $applications = Configuration::getInstance()->toArray("applications");
        array_unshift($applications, "Sharp");

        foreach ($applications as $appName)
            $this->installDependenciesInApp($appName);
    }

    protected function installDependenciesInApp(string $appName)
    {
        $appPath = Utils::relativePath($appName);
        $app = new Storage($appPath);

        if (!is_dir($appPath))
            return print("Cannot read [$appPath], inexistent directory");

        if (!$app->isFile("composer.json"))
            return print("Skipping [$appName] (no composer.json)\n");

        if ($app->isDirectory("vendor"))
            return print("Skipping [$appName] (Already installed)\n");

        echo "Installing in [$appName]\n";
        echo "---\n";
        $this->shellInDirectory("composer install", $appPath);
        echo "---\n";
    }
}