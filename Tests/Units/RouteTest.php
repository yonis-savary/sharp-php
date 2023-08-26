<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\Http\Request;
use Sharp\Classes\Http\Response;
use Sharp\Classes\Web\Route;
use Sharp\Tests\Classes\MiddlewareA;
use Sharp\Tests\Classes\MiddlewareB;

class RouteTest extends TestCase
{
    public function test_get()
    {
        $this->assertInstanceOf(Route::class, Route::get("/", fn()=>"A"));
    }

    public function test_post()
    {
        $this->assertInstanceOf(Route::class, Route::post("/", fn()=>"A"));
    }

    public function test_patch()
    {
        $this->assertInstanceOf(Route::class, Route::patch("/", fn()=>"A"));
    }

    public function test_put()
    {
        $this->assertInstanceOf(Route::class, Route::put("/", fn()=>"A"));
    }

    public function test_delete()
    {
        $this->assertInstanceOf(Route::class, Route::delete("/", fn()=>"A"));
    }

    public function test_getsetPath()
    {
        $myPath = "/A";
        $secondPath = "/B";
        $route = new Route($myPath, fn()=>null);

        $this->assertEquals($myPath, $route->getPath());
        $route->setPath($secondPath);
        $this->assertEquals($secondPath, $route->getPath());
    }

    public function test_getsetCallback()
    {
        $myCallback = fn()=>"A";
        $secondCallback = fn()=>"B";
        $route = new Route("/", $myCallback);

        $this->assertEquals($myCallback, $route->getCallback());
        $route->setCallback($secondCallback);
        $this->assertEquals($secondCallback, $route->getCallback());
    }

    public function test_getsetMethods()
    {
        $myMethods = ["A"];
        $secondMethods = ["B"];
        $route = new Route("/", fn()=>null, $myMethods);

        $this->assertEquals($myMethods, $route->getMethods());
        $route->setMethods($secondMethods);
        $this->assertEquals($secondMethods, $route->getMethods());
    }

    public function test_getsetMiddlewares()
    {
        $myMiddlewares = [MiddlewareA::class];
        $secondMiddlewares = [MiddlewareB::class];
        $route = new Route("/", fn()=>null, [], $myMiddlewares);

        $this->assertEquals($myMiddlewares, $route->getMiddlewares());
        $route->setMiddlewares($secondMiddlewares);
        $this->assertEquals($secondMiddlewares, $route->getMiddlewares());
    }

    public function test_getsetExtras()
    {
        $myExtras = ["A"];
        $secondExtras = ["B"];
        $route = new Route("/", fn()=>null, ["GET"], [], $myExtras);

        $this->assertEquals($myExtras, $route->getExtras());
        $route->setExtras($secondExtras);
        $this->assertEquals($secondExtras, $route->getExtras());
    }

    public function test_addMiddleware()
    {
        $myMiddlewares = [MiddlewareA::class];
        $secondMiddlewares = [MiddlewareA::class, MiddlewareB::class];
        $route = new Route("/", fn()=>null, [], $myMiddlewares);

        $this->assertEquals($myMiddlewares, $route->getMiddlewares());
        $route->addMiddlewares(MiddlewareB::class);
        $this->assertEquals($secondMiddlewares, $route->getMiddlewares());
    }


    /*
    public function test_match()
    {

    }*/

    public function test___invoke()
    {
        $dummyRequest = new Request("GET", "/");
        $route = new Route("/", fn()=> new Response(5, 200));

        $res = $route($dummyRequest);
        $this->assertEquals(5, $res->getContent());
    }

}