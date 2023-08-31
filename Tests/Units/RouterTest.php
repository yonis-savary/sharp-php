<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\Http\Request;
use Sharp\Classes\Http\Response;
use Sharp\Classes\Web\Route;
use Sharp\Classes\Web\Router;
use Sharp\Tests\Middlewares\RequestHasPostData;

class RouterTest extends TestCase
{

    public function test_route()
    {
        $r = new Router();

        $dummy = null;

        $r->addRoutes(
            Route::get("/", function() use (&$dummy) { $dummy="A"; }, []),
            Route::post("/", function() use (&$dummy) { $dummy="B"; }, []),
            Route::get("/home", fn() => Response::json("OK"), []),

            Route::get("/{int:n}", function($_, int $n) use (&$dummy) { $dummy=$n; }, [])
        );

        $r->route(new Request("GET", "/"));
        $this->assertEquals("A", $dummy);

        $r->route(new Request("POST", "/"));
        $this->assertEquals("B", $dummy);

        $res = $r->route(new Request("GET", "/home"));
        $this->assertInstanceOf(Response::class, $res);

        $r->route(new Request("GET", "/50"));
        $this->assertEquals(50, $dummy);

        $r->route(new Request("GET", "/25"));
        $this->assertEquals(25, $dummy);
    }

    public function test_group()
    {
        $r = new Router();

        $r->group([
            "path" => "api",
            "middlewares" => RequestHasPostData::class
        ], function($r) {
            $r->addRoutes(
                Route::get("/", fn()=>false),
                Route::post("/login", fn()=>false)
            );
        });

        $this->assertCount(2, $r->getRoutes());
        foreach ($r->getRoutes() as $route)
        {
            $this->assertStringStartsWith("/api", $route->getPath());
            $this->assertEquals([RequestHasPostData::class], $route->getMiddlewares());
        }
    }

    /*
     * public function test_addRoutes() {}
     * This method is implicitly tested by the others tests of this class
     */

}