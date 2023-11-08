<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\Env\Cache;
use Sharp\Classes\Env\Storage;
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

            Route::get("/{int:n}", function($_, int $n) use (&$dummy) { $dummy=$n; }, []),
            Route::get("/slug/{int:n}", function(Request $req) use (&$dummy) { $dummy = $req->getSlugs()["n"]; }, []),
            Route::get("/slug-name/{int:n}", function(Request $req) use (&$dummy) { $dummy = $req->getSlug("n"); }, []),
        );

        $r->route(new Request("GET", "/"));
        $this->assertEquals("A", $dummy);

        $r->route(new Request("POST", "/"));
        $this->assertEquals("B", $dummy);

        $res = $r->route(new Request("GET", "/home"));
        $this->assertInstanceOf(Response::class, $res);

        $r->route(new Request("GET", "/1"));
        $this->assertEquals(1, $dummy);

        $r->route(new Request("GET", "/2"));
        $this->assertEquals(2, $dummy);

        $r->route(new Request("GET", "/slug/3"));
        $this->assertEquals(3, $dummy);

        $r->route(new Request("GET", "/slug-name/4"));
        $this->assertEquals(4, $dummy);
    }

    public function test_group()
    {
        $router = new Router();

        $assertRoutesAreGrouped = function(Router $router) {
            $this->assertCount(2, $router->getRoutes());
            foreach ($router->getRoutes() as $route)
            {
                $this->assertStringStartsWith("/api", $route->getPath());
                $this->assertEquals([RequestHasPostData::class], $route->getMiddlewares());
            }
        };

        $group = [
            "path" => "api",
            "middlewares" => RequestHasPostData::class
        ];

        $router = new Router();

        $router->groupCallback($group, function(Router $router){
            $router->addRoutes(
                Route::view("/about", "about"),
                Route::view("/contact", "contact")
            );
        });

        $assertRoutesAreGrouped($router);
        $router = new Router();

        $router->addRoutes(
            ...$router->group(
                $group,
                Route::view("/about", "about"),
                Route::view("/contact", "contact")
            )
        );

        $assertRoutesAreGrouped($router);
        $router = new Router();

        $router->addGroup(
            $group,
            Route::view("/about", "about"),
            Route::view("/contact", "contact")
        );

        $assertRoutesAreGrouped($router);
    }

    /*
     * public function test_addRoutes() {}
     * This method is implicitly tested by the others tests of this class
     */


    public static function sampleCallbackA()
    {
        return Response::json("A");
    }

    public static function sampleCallbackB()
    {
        return Response::json("B");
    }


    /**
     * Test if the Router cache correctly routes that have the same path but
     * support different methods
     */
    public function test_issue_cached_same_path_different_methods()
    {
        $cache = new Cache(Storage::getInstance()->getSubStorage("test_router_issue_1"));

        $router = new Router($cache);
        $router->setConfiguration(["cached" => true]);

        $this->assertTrue($router->isCached());

        $router->addRoutes(
            Route::get("/", [self::class, "sampleCallbackA"]),
            Route::post("/", [self::class, "sampleCallbackB"])
        );

        $res = $router->route( new Request("GET", "/") );
        $this->assertEquals("A", $res->getContent());
        $this->assertCount(1, $cache->getKeys());

        $res = $router->route( new Request("POST", "/") );
        $this->assertEquals("B", $res->getContent());
        $this->assertCount(2, $cache->getKeys());

    }

    public function test_issue_same_path_with_suffix()
    {
        $router = new Router();

        $dummy = 0;

        $router->addRoutes(
            Route::get("/{id}", function($_, int $id) use (&$dummy) { $dummy = $id; }),
            Route::get("/{id}/suffix", fn() => null  /* Do nothing !*/)
        );

        $router->route(new Request("GET", "/5"));
        $this->assertEquals(5, $dummy);

        $router->route(new Request("GET", "/10"));
        $this->assertEquals(10, $dummy);

        $router->route(new Request("GET", "/9999/suffix"));
        $this->assertEquals(10, $dummy);
    }


    public function test_match()
    {
        $router = new Router();

        $dummyCallback = fn()=>false;

        // Any path - any method
        $anyRoute = new Route("/", $dummyCallback);
        $this->assertTrue($router->match($anyRoute, new Request("GET", "/")));
        $this->assertTrue($router->match($anyRoute, new Request("POST", "/")));

        // Specific path - any method
        $postRoute = new Route("/A", $dummyCallback);
        $this->assertFalse($router->match($postRoute, new Request("GET", "/")));
        $this->assertTrue($router->match($postRoute, new Request("GET", "/A")));

        // Specific path - Specific method
        $postRoute = new Route("/A", $dummyCallback, ["POST"]);
        $this->assertFalse($router->match($postRoute, new Request("GET", "/A")));
        $this->assertTrue($router->match($postRoute, new Request("POST", "/A")));
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
        $router = new Router();
        $route = Route::get($routePath, fn()=>false);

        foreach ($failRequestPath as $path)
        {
            $req = new Request("GET", $path);
            $this->assertFalse($router->match($route, $req), "Failed fail Request for [$routePath] route");
        }

        $req = new Request("GET", $successRequestPath);
        $this->assertTrue($router->match($route, $req), "Failed success Request for [$routePath] route");
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