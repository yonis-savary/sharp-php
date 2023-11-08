<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\Http\Request;
use Sharp\Classes\Http\Response;
use Sharp\Classes\Web\Route;
use Sharp\Core\Utils;
use Sharp\Tests\Classes\MiddlewareA;
use Sharp\Tests\Classes\MiddlewareB;

class RouteTest extends TestCase
{
    public function test_get()
    {
        $route = Route::get("/", fn()=>"A");
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals(["GET"], $route->getMethods());
    }

    public function test_post()
    {
        $route = Route::post("/", fn()=>"A");
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals(["POST"], $route->getMethods());
    }

    public function test_patch()
    {
        $route = Route::patch("/", fn()=>"A");
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals(["PATCH"], $route->getMethods());
    }

    public function test_put()
    {
        $route = Route::put("/", fn()=>"A");
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals(["PUT"], $route->getMethods());
    }

    public function test_delete()
    {
        $route = Route::delete("/", fn()=>"A");
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals(["DELETE"], $route->getMethods());
    }

    public function test_getSetPath()
    {
        $myPath = "/A";
        $secondPath = "/B";
        $route = new Route($myPath, fn()=>null);

        $this->assertEquals($myPath, $route->getPath());
        $route->setPath($secondPath);
        $this->assertEquals($secondPath, $route->getPath());
    }

    public function test_getSetCallback()
    {
        $myCallback = fn()=>"A";
        $secondCallback = fn()=>"B";
        $route = new Route("/", $myCallback);

        $this->assertEquals($myCallback, $route->getCallback());
        $route->setCallback($secondCallback);
        $this->assertEquals($secondCallback, $route->getCallback());
    }

    public function test_getSetMethods()
    {
        $myMethods = ["A"];
        $secondMethods = ["B"];
        $route = new Route("/", fn()=>null, $myMethods);

        $this->assertEquals($myMethods, $route->getMethods());
        $route->setMethods($secondMethods);
        $this->assertEquals($secondMethods, $route->getMethods());
    }

    public function test_getSetMiddlewares()
    {
        $myMiddlewares = [MiddlewareA::class];
        $secondMiddlewares = [MiddlewareB::class];
        $route = new Route("/", fn()=>null, [], $myMiddlewares);

        $this->assertEquals($myMiddlewares, $route->getMiddlewares());
        $route->setMiddlewares($secondMiddlewares);
        $this->assertEquals($secondMiddlewares, $route->getMiddlewares());
    }

    public function test_getSetExtras()
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

    public function test___invoke()
    {
        $dummyRequest = new Request("GET", "/");
        $route = new Route("/", fn()=> new Response(5, 200));

        $res = $route($dummyRequest);
        $this->assertEquals(5, $res->getContent());
    }

    public function test_file()
    {
        $relPath = "Sharp/Tests/Classes/A.php";
        $absPath = Utils::relativePath($relPath);

        $route = Route::file("/my-file", $relPath);
        $req = new Request("GET", "/my-file");

        /** @var Response $response */
        $response = $route($req);

        $this->assertEquals($absPath, $response->getContent());

        ob_start();
        $response->display(false);
        $displayed = ob_get_clean();

        $this->assertStringContainsString("class A", $displayed);
    }
}