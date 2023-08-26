<?php

namespace Sharp\Extensions\AssetsKit\Tests;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\Http\Request;
use Sharp\Classes\Http\Response;
use Sharp\Extensions\AssetsKit\Components\Svg;

class SvgTest extends TestCase
{
    public function test_handleRequest()
    {
        $svg = new Svg();
        $req = new Request("GET", "/assets/svg", ["name" => "person"]);

        $this->assertInstanceOf(
            Response::class,
            $svg->handleRequest($req, true)
        );
    }


    public function test_serve()
    {
        $svg = new Svg();
        $req = new Request("GET", "/assets/svg", ["name" => "person"]);

        $this->assertInstanceOf(
            Response::class,
            $svg->serve($req)
        );
    }

    public function test_get()
    {
        $svg = new Svg();
        $svgContent = $svg->get("person");

        $this->assertStringContainsString("<svg", $svgContent);
    }

    public function test_exists()
    {
        $svg = new Svg();

        $this->assertTrue($svg->exists("person"));
        $this->assertTrue($svg->exists("file"));
        $this->assertFalse($svg->exists("file-exclamation-triange"));
    }
}