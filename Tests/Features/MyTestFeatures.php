<?php

namespace Sharp\Tests\Features;

use Sharp\Classes\Web\Controller;
use Sharp\Classes\Web\Route;
use Sharp\Classes\Web\Router;

class MyTestFeatures
{
    use Controller;

    public static function declareRoutes(Router $router)
    {
        $router->addRoutes(
            Route::view("/", self::relativePath("FeatureView.php"))
        );
    }
}