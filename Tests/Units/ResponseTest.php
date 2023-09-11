<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\Core\Logger;
use Sharp\Classes\Env\Storage;
use Sharp\Classes\Http\Response;
use Sharp\Classes\Web\Route;

class ResponseTest extends TestCase
{
    const DUMMY_HEADERS = [
        "cache-control" => "no-store, no-cache, must-revalidate",
        "connection" => "Keep-Alive",
        "content-encoding" => "gzip",
        "content-length" => "23800",
        "content-type" => "text/html;charset=UTF-8"
    ];

    public function test_logSelf()
    {
        $stdLogger = Logger::fromStream(fopen("php://output", "w"));

        $capture = function($function){
            ob_start();
            $function();
            return ob_get_clean();
        };

        $response = new Response(null, 500, ["Content-Type" => "text/html"]);
        $output = $capture(fn() => $response->logSelf($stdLogger));
        $this->assertStringContainsString("500 text/html", $output);

        $response = new Response(null, 200, ["Content-Type" => "application/javascript"]);
        $output = $capture(fn() => $response->logSelf($stdLogger));
        $this->assertStringContainsString("200 application/javascript", $output);
    }

    public function test_getContent()
    {
        $response = new Response([1,2,3]);
        $this->assertEquals([1,2,3], $response->getContent());

        foreach (range(1, 10) as $i)
        {
            $str = bin2hex(random_bytes($i));

            $response = new Response($str);
            $this->assertEquals($str, $response->getContent());
        }
    }


    public function test_withHeaders()
    {
        $headers = self::DUMMY_HEADERS;

        $response = new Response();
        foreach ($headers as $name => $value)
            $response->withHeaders([$name => $value]);
        $this->assertEquals($headers, $response->getHeaders());

        $response = new Response();
        $response->withHeaders($headers);
        $this->assertEquals($headers, $response->getHeaders());
    }

    public function test_removeHeaders()
    {
        $response = new Response(null, 200, ["content-type" => "application/json"]);
        $response->removeHeaders(["Content-Type"]);
        $this->assertEquals([], $response->getHeaders());

        $response = new Response(null, 200, ["Content-Type" => "application/json"]);
        $response->removeHeaders(["content-type"]);
        $this->assertEquals([], $response->getHeaders());
    }


    public function test_getHeaders()
    {
        $headers = self::DUMMY_HEADERS;

        $response = new Response();
        $response->withHeaders($headers);

        $actualHeaders = $response->getHeaders();
        $this->assertEquals($actualHeaders, $response->getHeaders());
    }

    public function test_getHeader()
    {
        $response = new Response(null, 200, [
            "Content-Type" => "text/html",
            "Connection" => "Keep-Alive"
        ]);

        $this->assertEquals("text/html", $response->getHeader("Content-Type"));
        $this->assertEquals("Keep-Alive", $response->getHeader("Connection"));
    }

    public function test_display()
    {
        $capture = function($function){
            ob_start();
            $function();
            return ob_get_clean();
        };

        $response = new Response();
        $getResponseOutput = function() use (&$response, $capture) {
            return $capture(fn() => $response->display(false));
        };

        $response = Response::json([1,2,3]);
        $this->assertStringContainsString("[1,2,3]", $getResponseOutput());

        $response = Response::json(["A" => 5]);
        $this->assertStringContainsString("{\"A\":5}", $getResponseOutput());

        $response = Response::html("Hello");
        $this->assertStringContainsString("Hello", $getResponseOutput());
    }


    public function test_html()
    {
        $response = Response::html("Hello");
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals("Hello", $response->getContent());
        $this->assertEquals("text/html", $response->getHeaders()["content-type"]);
    }

    public function test_file()
    {
        $storage = Storage::getInstance();
        $storage->write("output.txt", "Hello");

        $response = Response::file($storage->path("output.txt"));
        $this->assertInstanceOf(Response::class, $response);

        ob_start();
        $response->display(false);
        $output = ob_get_clean();

        $this->assertEquals("Hello", $output);
    }

    public function test_json()
    {
        $response = Response::json([1,2,3]);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals([1,2,3], $response->getContent());
    }

    public function test_redirect()
    {
        $response = Response::redirect("/another-one");
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals("/another-one", $response->getHeaders()["location"]);
    }

    public function test_adapt()
    {
        $this->assertInstanceOf(Response::class, Response::adapt(123));
        $this->assertInstanceOf(Response::class, Response::adapt("ABC"));
        $this->assertInstanceOf(Response::class, Response::adapt(["ABC" => 123]));

        $this->assertInstanceOf(Response::class, Response::adapt(null));
        $this->assertInstanceOf(Response::class, Response::adapt(new Route("/", fn()=>null)));
    }
}