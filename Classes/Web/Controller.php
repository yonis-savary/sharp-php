<?php

namespace Sharp\Classes\Web;

/**
 * Using this trait allows you to use those methods in your class
 *  - self::asset() : Find an asset inside controller's app Asset directory
 *  - self::view() : Find a view inside controller's app View directory
 *  - self::declareRoutes() : Declare controller's routes (automatically called)
 */
trait Controller
{
    public static function declareRoutes(Router $router)
    {

    }
}