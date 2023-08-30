<?php

namespace Sharp\Classes\Web;

use Sharp\Core\Utils;

/**
 * Using this trait allows you to use those methods in your class
 *  - self::asset() : Find an asset inside controller's app Asset directory
 *  - self::view() : Find a view inside controller's app View directory
 *  - self::declareRoutes() : Declare controller's routes (automatically called)
 */
trait Controller
{
    public static function getAppPath(): string
    {
        return Utils::relativePath(
            Utils::classnameToPath(preg_replace("/Controllers\\\\.+/", "", self::class))
        );
    }

    public static function relativePath(string $path): string
    {
        return Utils::joinPath(self::getAppPath(), $path);
    }

    protected static function findFile(string $directory, string $filename): string|false
    {
        $assetsDir = self::relativePath($directory);

        if (!is_dir($assetsDir))
            return false;

        foreach (Utils::exploreDirectory($assetsDir, Utils::ONLY_FILES) as $file)
        {
            if (str_ends_with($file, $filename))
                return $file;
        }

        return false;
    }

    public static function asset(string $assetName): string|false
    {
        return self::findFile("Assets", $assetName);
    }

    public static function view(string $viewName): string|false
    {
        if (!str_ends_with($viewName, ".php"))
            $viewName .= ".php";

        return self::findFile("Views", $viewName);
    }

    public static function declareRoutes()
    {
    }
}