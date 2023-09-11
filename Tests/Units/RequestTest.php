<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\Http\Classes\UploadFile;
use Sharp\Classes\Http\Request;
use Sharp\Classes\Web\Route;
use Sharp\Classes\Env\Storage;

class RequestTest extends TestCase
{
    protected function mockPHPUpload(int $n=1): array
    {
        $storage = Storage::getInstance();
        $files = [];
        for ($i=0; $i<$n; $i++)
        {
            $name = uniqid("upload");
            $path = $storage->path("uploads/$name");
            $content = "content";
            $storage->write("uploads/$name", $content);

            $files[] = [
                "name" => $name,
                "type" => "text/plain",
                "tmp_name" => $path,
                "error" => UPLOAD_ERR_OK,
                "size" => strlen($content)
            ];
        }

        if ($n > 1)
        {
            $uploads = ["uploads" => [
                "name"     => [],
                "type"     => [],
                "tmp_name" => [],
                "error"    => [],
                "size"     => []
            ]];
            foreach ($uploads["uploads"] as $key => &$value)
                $value = array_map(fn($x) => $x[$key], $files);

            return $uploads;
        }
        else
        {
            // Single upload
            return ["uploads" => $files[0]];
        }
    }

    protected function sampleGetRequest(): Request
    {
        return new Request("GET", "/view", ["A" => 1]);
    }

    protected function samplePostRequest(bool $multipleUploads=false): Request
    {
        return new Request("POST", "/form", ["A" => 1], ["B" => 2], $this->mockPHPUpload($multipleUploads ? 5:1));
    }

    public function test_buildFromGlobals()
    {
        $this->assertInstanceOf(
            Request::class,
            Request::buildFromGlobals()
        );
    }

    public function test_post()
    {
        $get = $this->sampleGetRequest();
        $post = $this->samplePostRequest();

        $this->assertEquals([], $get->post());
        $this->assertEquals(["B" => 2], $post->post());
    }

    public function test_get()
    {
        $get = $this->sampleGetRequest();
        $post = $this->samplePostRequest();

        $this->assertEquals(["A" => 1], $get->get());
        $this->assertEquals(["A" => 1], $post->get());
    }

    public function test_all()
    {
        $get = $this->sampleGetRequest();
        $post = $this->samplePostRequest();

        $this->assertEquals(["A" => 1], $get->all());
        $this->assertEquals(["A" => 1, "B" => 2], $post->all());
    }

    public function test_list()
    {
        $get = $this->sampleGetRequest();
        $post = $this->samplePostRequest();

        list($A, $B, $C) = $get->list("A", "B", "C");
        $this->assertEquals(1, $A);
        $this->assertNull($B);
        $this->assertNull($C);

        list($A, $B, $C) = $post->list("A", "B", "C");
        $this->assertEquals(1, $A);
        $this->assertEquals(2, $B);
        $this->assertNull($C);
    }

    public function test_params()
    {
        $get = $this->sampleGetRequest();
        $post = $this->samplePostRequest();

        $this->assertEquals(1, $get->params("A"));
        $this->assertNull($get->params("B"));
        $this->assertNull($get->params("C"));

        $this->assertEquals(1, $post->params("A"));
        $this->assertEquals(2, $post->params("B"));
        $this->assertNull($post->params("C"));
    }

    public function test_paramsFromGet()
    {
        $get = $this->sampleGetRequest();
        $post = $this->samplePostRequest();

        $this->assertEquals(1, $get->paramsFromGet("A"));
        $this->assertNull($get->paramsFromGet("B"));
        $this->assertNull($get->paramsFromGet("C"));

        $this->assertEquals(1, $post->paramsFromGet("A"));
        $this->assertNull($post->paramsFromGet("B"));
        $this->assertNull($post->paramsFromGet("C"));
    }

    public function test_paramsFromPost()
    {
        $get = $this->sampleGetRequest();
        $post = $this->samplePostRequest();

        $this->assertNull($get->paramsFromPost("A"));
        $this->assertNull($get->paramsFromPost("B"));
        $this->assertNull($get->paramsFromPost("C"));

        $this->assertNull($post->paramsFromPost("A"));
        $this->assertEquals(2, $post->paramsFromPost("B"));
        $this->assertNull($post->paramsFromPost("C"));
    }

    public function test_getMethod()
    {
        $get = $this->sampleGetRequest();
        $post = $this->samplePostRequest();

        $this->assertEquals("GET", $get->getMethod());
        $this->assertEquals("POST", $post->getMethod());
    }

    public function test_getPath()
    {
        $get = $this->sampleGetRequest();
        $post = $this->samplePostRequest();

        $this->assertEquals("/view", $get->getPath());
        $this->assertEquals("/form", $post->getPath());
    }

    public function test_getHeaders()
    {
        $req = new Request("GET", "/", [], [], [], ["H1" => "V1"]);
        $this->assertEquals(["H1" => "V1"], $req->getHeaders());
    }

    public function test_getUploads()
    {
        $req = $this->samplePostRequest(true);
        $this->assertCount(5, $req->getUploads());
        foreach ($req->getUploads() as $upload)
            $this->assertInstanceOf(UploadFile::class, $upload);

        $req = $this->samplePostRequest();
        $this->assertCount(1, $req->getUploads());
        foreach ($req->getUploads() as $upload)
            $this->assertInstanceOf(UploadFile::class, $upload);
    }

    public function test_setSlugs()
    {
        $req = $this->sampleGetRequest();

        $req->setSlugs(["name" => "value"]);
        $this->assertEquals(["name" => "value"], $req->getSlugs());
    }

    public function test_getSlugs()
    {
        $req = $this->sampleGetRequest();

        $this->assertEquals([], $req->getSlugs());

        $req->setSlugs(["name" => "value"]);
        $this->assertEquals(["name" => "value"], $req->getSlugs());
    }

    public function test_getSlug()
    {
        $req = $this->sampleGetRequest();

        $req->setSlugs(["name" => "value", "nullKey" => null]);
        $this->assertEquals("value", $req->getSlug("name"));
        $this->assertNull($req->getSlug("nullKey"));
        $this->assertNull($req->getSlug("nullKey", -1));
        $this->assertEquals(-1, $req->getSlug("inexistant", -1));
    }

    public function test_setRoute()
    {
        $route = new Route("/", fn()=>"null");
        $req = new Request("GET", "/");

        $req->setRoute($route);
        $this->assertEquals($req->getRoute(), $route);
    }

    public function test_getRoute()
    {
        $route = new Route("/", fn()=>"null");
        $req = new Request("GET", "/");

        $this->assertNull($req->getRoute());
        $req->setRoute($route);
        $this->assertEquals($req->getRoute(), $route);
    }

    public function test_unset()
    {
        $req = $this->samplePostRequest();

        $this->assertEquals(["A" => 1, "B" => 2], $req->all());
        $req->unset("B");

        $this->assertEquals(["A" => 1], $req->all());
        $req->unset("A");

        $this->assertEquals([], $req->all());
    }
}