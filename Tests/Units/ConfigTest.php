<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\Env\Config;
use Sharp\Classes\Env\Storage;

class ConfigTest extends TestCase
{
    // Most of Config feature are tested by [./AbstractMapTest.php]

    public function test___construct()
    {
        $storage = Storage::getInstance();

        $storage->write(
            "config-test-construct.json",
            json_encode(["A" => 5])
        );

        $config = new Config($storage->path("config-test-construct.json"));
        $this->assertEquals(5, $config->get("A"));
    }

    public function test_save()
    {
        $storage = Storage::getInstance();

        $file = $storage->path("config-test.json");

        $unrelated = new Config();
        $unrelated->set("key", "A");
        $unrelated->save($file);

        $fromFile = new Config($file);
        $this->assertEquals("A", $fromFile->get("key"));
    }
}
