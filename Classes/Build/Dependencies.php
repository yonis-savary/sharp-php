<?php

namespace Sharp\Classes\Build;

use Sharp\Classes\CLI\AbstractBuildTask;
use Sharp\Classes\Env\Config;
use Sharp\Core\Utils;

class Dependencies extends AbstractBuildTask
{
    public function execute()
    {
        echo "Installing dependencies...\n";
        $applications = Config::getInstance()->toArray("applications");
        array_unshift($applications, "Sharp", ...glob("Sharp/Extensions/*"));

        foreach ($applications as $appName)
        {
            $appPath = Utils::relativePath($appName);

            if (!is_dir($appPath))
            {
                echo "Cannot read [$appPath], inexistant directory";
                continue;
            }

            $composer = Utils::joinPath($appPath, "composer.json");
            $vendor = Utils::joinPath($appPath, "vendor");

            if (!is_file($composer))
            {
                echo "Skipping [$appName] (no composer.json)\n";
                continue;
            }

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
    }
}