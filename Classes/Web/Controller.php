<?php

namespace Sharp\Classes\Web;

use Sharp\Classes\Core\Logger;
use Sharp\Core\Utils;

/**
 * Using this trait allows you to use those methods in your class
 *  - self::asset() : Find an asset inside controller's app Asset directory
 *  - self::view() : Find a view inside controller's app View directory
 *  - self::declareRoutes() : Declare controller's routes (automatically called)
 */
trait Controller
{
    public static function relativePath(string $path): string
    {
        $controllerPath = preg_replace("/[^\/]+$/", "", Utils::classnameToPath(self::class));
        $file = Utils::relativePath(Utils::joinPath($controllerPath, $path));

        if (is_file($file))
            Logger::getInstance()->warning("[$file] file does not exists !");

        return $file;
    }

    public static function declareRoutes(Router $router)
    {

    }
}