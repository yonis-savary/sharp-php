<?php

namespace Sharp\Commands\Troubleshoot\Contract;

use Sharp\Classes\Env\Storage;
use Sharp\Core\Autoloader;

abstract class AbstractCodeChecker
{
    /**
     * Check a file of any purpose
     * @return bool|string return `true` on valid file, `false|string` on error (error message)
     */
    public function checkFile(string $file, string|false $purpose): string|bool
    {
        return true;
    }

    /**
     * Check a file that is in the autoloader
     * @return bool|string return `true` on valid file, `string` on error (error message)
     */
    public function checkClassFile(string $file): string|bool
    {
        return true;
    }

    /**
     * Check a view file
     * @return bool|string return `true` on valid file, `string` on error (error message)
     */
    public function checkViewFile(string $file): string|bool
    {
        return true;
    }

    /**
     * Check an asset file
     * @return bool|string return `true` on valid file, `string` on error (error message)
     */
    public function checkAssetFile(string $file): string|bool
    {
        return true;
    }

    /**
     * Check an helper or route file
     * @return bool|string return `true` on valid file, `string` on error (error message)
     */
    public function checkHelperFile(string $file): string|bool
    {
        return true;
    }

    public abstract function getPurposeMessage(): string;

    public abstract function getErrorMessage(): string;

    public abstract function getSuccessMessage(): string;

    public function checkApplication(string $application)
    {
        $green = "\033[32;1m";
        $yellow = "\033[33;1m";
        $reset = "\033[0m";

        $applicationStorage = new Storage($application);

        $errors = [];

        foreach ($applicationStorage->listDirectories() as $directory)
        {
            $purpose = Autoloader::DIRECTORIES_PURPOSE[basename($directory)] ?? false;

            $callBack = null;

            switch ($purpose)
            {
                case Autoloader::AUTOLOAD: $callBack = "checkClassFile"; break;
                case Autoloader::VIEWS: $callBack = "checkViewFile"; break;
                case Autoloader::ASSETS: $callBack = "checkAssetFile"; break;
                case Autoloader::REQUIRE:
                case Autoloader::ROUTES: $callBack = "checkHelperFile"; break;
            }

            $dirStorage = $applicationStorage->getSubStorage($directory);
            foreach ($dirStorage->exploreDirectory("/", Storage::ONLY_FILES) as $file)
            {
                $errors[] = $this->checkFile($file, $purpose);
                if ($callBack)
                    $errors[] = $this->$callBack($file);
            }
        }


        $errors = array_filter($errors, is_string(...));
        if (!count($errors))
            return print( $green . "✓ [$application] ". $this->getSuccessMessage() . $reset. "\n");

        print( $yellow ."✗ [$application] ". $this->getErrorMessage() . $reset . "\n");
        foreach ($errors as $error)
        {
            echo " - $error\n";
        }

        return;
    }
}