<?php

namespace Sharp\Tests\Features\SubFeature;

use Sharp\Classes\Web\Controller;
use Sharp\Classes\Web\Route;
use Sharp\Classes\Web\Router;

class MyTestSubFeature
{
    use Controller;

    public static function declareRoutes(Router $router)
    {
        $router->addRoutes(
            Route::view("/", self::relativePath("SubFeatureView.php"))
        );
    }
}