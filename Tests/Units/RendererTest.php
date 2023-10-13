<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\Web\Renderer;
use Sharp\Core\Utils;

class RendererTest extends TestCase
{
    public function test_findTemplatePath()
    {
        $r = Renderer::getInstance();

        $expectedPath = Utils::relativePath("Sharp/Tests/Views/sharp-tests/sharp-tests-child.php");

        $this->assertEquals($expectedPath, $r->findTemplate("sharp-tests-child"));
        $this->assertEquals($expectedPath, $r->findTemplate("sharp-tests/sharp-tests-child"));
        $this->assertFalse($r->findTemplate("bad-subdir/sharp-tests-child"));
    }

    public function test_templateExists()
    {
        $r = Renderer::getInstance();

        $this->assertTrue($r->templateExists("sharp-tests-child"));
        $this->assertTrue($r->templateExists("sharp-tests/sharp-tests-child"));
        $this->assertFalse($r->templateExists("bad-subdir/sharp-tests-child"));
    }

    public function test_render()
    {
        $content = Renderer::getInstance()->render("sharp-tests-child", ["variable" => "VARIABLE"]);

        $this->assertTrue(substr_count($content, "CHILD") == 1);
        $this->assertTrue(substr_count($content, "PARENT") == 1);
        $this->assertTrue(substr_count($content, "COMPONENT") == 2);
        $this->assertTrue(substr_count($content, "VARIABLE") == 1);
    }
}