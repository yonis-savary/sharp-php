<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\Env\Configuration;
use Sharp\Classes\Env\Storage;

class ConfigTest extends TestCase
{
    // Most of Configuration feature are tested by [./AbstractMapTest.php]

    public function test___construct()
    {
        $storage = Storage::getInstance();

        $storage->write(
            "config-test-construct.json",
            json_encode(["A" => 5])
        );

        $config = new Configuration($storage->path("config-test-construct.json"));
        $this->assertEquals(5, $config->get("A"));
    }

    public function test_save()
    {
        $storage = Storage::getInstance();

        $file = $storage->path("config-test.json");

        $unrelated = new Configuration();
        $unrelated->set("key", "A");
        $unrelated->save($file);

        $fromFile = new Configuration($file);
        $this->assertEquals("A", $fromFile->get("key"));
    }
}
