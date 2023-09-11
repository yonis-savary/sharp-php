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


    public function test_match()
    {
        $dummyCallback = fn()=>false;

        // Any path - any method
        $anyRoute = new Route("/", $dummyCallback);
        $this->assertTrue($anyRoute->match(new Request("GET", "/")));
        $this->assertTrue($anyRoute->match(new Request("POST", "/")));

        // Specific path - any method
        $postRoute = new Route("/A", $dummyCallback);
        $this->assertFalse($postRoute->match(new Request("GET", "/")));
        $this->assertTrue($postRoute->match(new Request("GET", "/A")));

        // Specific path - Specific method
        $postRoute = new Route("/A", $dummyCallback, ["POST"]);
        $this->assertFalse($postRoute->match(new Request("GET", "/A")));
        $this->assertTrue($postRoute->match(new Request("POST", "/A")));
    }

    public function test___invoke()
    {
        $dummyRequest = new Request("GET", "/");
        $route = new Route("/", fn()=> new Response(5, 200));

        $res = $route($dummyRequest);
        $this->assertEquals(5, $res->getContent());
    }




    const FORMAT_SAMPLES = [
        "int"      => "/5",
        "float"    => "/5.398",
        "any"      => "/I'am a complete sentence !",
        "date"     => "/2023-07-16",
        "time"     => "/16:20:00",
        "datetime" => "/2000-10-01 15:00:00",
    ];

    protected function genericSlugFormatTest(
        string $routePath,
        string $successRequestPath,
        array $failRequestPath,
    ) {
        $route =Route::get($routePath, fn()=>false);

        foreach ($failRequestPath as $path)
        {
            $req = new Request("GET", $path);
            $this->assertFalse($route->match($req), "Failed fail Request for [$routePath] route");
        }

        $req = new Request("GET", $successRequestPath);
        $this->assertTrue($route->match($req), "Failed success Request for [$routePath] route");
    }

    public function test_slugFormats()
    {
        $samples = self::FORMAT_SAMPLES;

        $samplesWithout = function($keys) use ($samples) {
            $copy = $samples;
            foreach ($keys as $k)
                unset($copy[$k]);
            return array_values($copy);
        };

        $this->genericSlugFormatTest("/{int:x}",      $samples["int"],  $samplesWithout(["int"]));
        $this->genericSlugFormatTest("/{float:x}",    $samples["float"],  $samplesWithout(["float", "int"]));
        $this->genericSlugFormatTest("/{any:x}",      $samples["any"],  []);
        $this->genericSlugFormatTest("/{date:x}",     $samples["date"],  $samplesWithout(["date"]));
        $this->genericSlugFormatTest("/{time:x}",     $samples["time"],  $samplesWithout(["time"]));
        $this->genericSlugFormatTest("/{datetime:x}", $samples["datetime"],  $samplesWithout(["datetime"]));
    }

}