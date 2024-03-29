<?php

namespace Sharp\Tests\Units;

use Exception;
use PHPUnit\Framework\TestCase;
use Sharp\Classes\Env\Cache;
use Sharp\Classes\Env\Classes\CacheElement;
use Sharp\Classes\Env\Storage;
use Sharp\Core\Utils;

class CacheTest extends TestCase
{
    protected function getDummyCache(): Cache
    {
        $storage = Storage::getInstance();
        return new Cache($storage->getSubStorage(uniqid("cache-test")));
    }

    protected function getDummyArray(): array
    {
        $array = [];
        for ($i=0; $i<5; $i++)
            $array[uniqid()] = uniqid();
        return $array;
    }

    public function test_has()
    {
        $cache = $this->getDummyCache();

        $this->assertFalse($cache->has("my-data"));

        $array = $this->getDummyArray();
        $cache->set("my-data", $array);

        $this->assertTrue($cache->has("my-data"));
    }

    public function test_get()
    {
        $cache = $this->getDummyCache();

        $this->assertNull($cache->get("my-data"));

        $array = $this->getDummyArray();
        $cache->set("my-data", $array);

        $this->assertEquals($array, $cache->get("my-data"));
    }

    public function test_try()
    {
        $cache = $this->getDummyCache();

        $success = null;

        if ($_ = $cache->try("key"))
            throw new Exception("This block shouldn't be reached !");
        else
            $success = 0;

        $cache->set("key", 5);

        if ($value = $cache->try("key"))
            $success += $value;
        else
            throw new Exception("This block shouldn't be reached !");

        $this->assertEquals(5, $success);
    }

    public function test_set()
    {
        $cache = $this->getDummyCache();

        $cache->set("key", "A");
        $this->assertEquals("A", $cache->get("key"));

        $cache->set("key", "B");
        $this->assertEquals("B", $cache->get("key"));
    }

    public function test_delete()
    {
        $cache = $this->getDummyCache();

        $cache->set("key", "A");
        $this->assertEquals("A", $cache->get("key"));

        $cache->delete("key");
        $this->assertNull($cache->get("key"));
    }

    public function test_expire()
    {
        $storage = Storage::getInstance();

        $element = new CacheElement("my-key");
        $element->setContent("Hello", 2);
        $filename = $element->save($storage);

        $this->assertIsString($filename);
        $this->assertInstanceOf(CacheElement::class, CacheElement::fromFile($filename));


        $expiredElement = new CacheElement("my-key", 1, time()-1);
        $expiredElement->setContent("Hello");
        $filename = $expiredElement->save($storage);

        $this->assertNull(CacheElement::fromFile($filename));
    }

    public function test_getReference()
    {
        $cache = $this->getDummyCache();

        $reference = &$cache->getReference("my-key");
        $reference = 5;

        $this->assertEquals(5, $cache->get("my-key"));

        $reference = 10;
        $this->assertEquals(10, $cache->get("my-key"));
    }

    public function test_getKeys()
    {
        $cache = $this->getDummyCache();

        $cache->set("A", true);
        $this->assertEquals(["A"], $cache->getKeys());

        $cache->set("B", true);
        $this->assertEquals(["A", "B"], $cache->getKeys());

        $cache->delete("A");
        $this->assertEquals(["B"], $cache->getKeys());
    }

    public function test_getSubCache()
    {
        $parent = $this->getDummyCache();
        $child = $parent->getSubCache("sub-cache");

        $this->assertInstanceOf(Cache::class, $child);
        $this->assertEquals(
            Utils::joinPath( $parent->getStorage()->getRoot(), "sub-cache" ),
            $child->getStorage()->getRoot()
        );
    }
}