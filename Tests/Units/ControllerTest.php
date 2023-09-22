<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\Http\Request;
use Sharp\Classes\Web\Router;
use Sharp\Tests\Features\MyTestFeatures;
use Sharp\Tests\Features\SubFeature\MyTestSubFeature;

class ControllerTest extends TestCase
{
    public function test_router()
    {
        $router = new Router();
        MyTestFeatures::declareRoutes($router);
        $response = $router->route(new Request("GET", "/"));
        $this->assertStringContainsString("Feature", $response->getContent());

        $router = new Router();
        MyTestSubFeature::declareRoutes($router);
        $response = $router->route(new Request("GET", "/"));
        $this->assertStringContainsString("SubFeature", $response->getContent());
    }
}