<?php

namespace Sharp\Tests\Units;

use Exception;
use PHPUnit\Framework\TestCase;
use Sharp\Classes\Core\AbstractMap;

class AbstractMapTest extends TestCase
{
    protected function getDummyAbstractMap(): AbstractMap
    {
        $class = new class extends AbstractMap {};
        return new $class();
    }

    public function test_get()
    {
        $dummy = $this->getDummyAbstractMap();

        $this->assertEquals("A", $dummy->get("key", "A"));

        $dummy->set("key", "B");
        $this->assertEquals("B", $dummy->get("key", "A"));
    }

    public function test_try()
    {
        $dummy = $this->getDummyAbstractMap();

        $success = null;

        if ($_ = $dummy->try("key"))
            throw new Exception("This block shouldn't be reached !");
        else
            $success = 0;

        $dummy->set("key", 5);

        if ($value = $dummy->try("key"))
            $success += $value;
        else
            throw new Exception("This block shouldn't be reached !");

        $this->assertEquals(5, $success);
    }

    public function test_set()
    {
        $dummy = $this->getDummyAbstractMap();

        $this->assertEquals("A", $dummy->get("key", "A"));

        $dummy->set("key", "B");
        $this->assertEquals("B", $dummy->get("key", "A"));

        $dummy->set("key", 5);
        $this->assertEquals(5, $dummy->get("key", "A"));
    }

    public function test_has()
    {
        $dummy = $this->getDummyAbstractMap();

        $this->assertFalse($dummy->has("key"));
        $dummy->set("key", "A");
        $this->assertTrue($dummy->has("key"));
    }

    public function test_unset()
    {
        $dummy = $this->getDummyAbstractMap();

        $dummy->set("key", "A");
        $this->assertTrue($dummy->has("key"));
        $this->assertEquals("A", $dummy->get("key", "B"));

        $dummy->unset("key");
        $this->assertFalse($dummy->has("key"));
        $this->assertEquals("B", $dummy->get("key", "B"));

        $dummy->unset("inexistent-key");
        $this->assertFalse($dummy->has("inexistent-key"));
    }

    public function test_toArray()
    {
        $dummy = $this->getDummyAbstractMap();

        $dummy->set("key", "A");
        $this->assertEquals(["A"], $dummy->toArray("key"));

        $dummy->set("key", [1, 2, 3]);
        $this->assertEquals([1, 2, 3], $dummy->toArray("key"));
    }

    public function test_edit()
    {
        $dummy = $this->getDummyAbstractMap();

        $dummy->edit("inexistent", fn($x) => $x + 5, 0);
        $this->assertEquals(5, $dummy->get("inexistent"));
        $dummy->edit("inexistent", fn($x) => $x + 5, 0);
        $this->assertEquals(10, $dummy->get("inexistent"));

        $dummy->set("existent", "Hello");
        $dummy->edit("existent", fn($x) => $x . " world");

        $this->assertEquals("Hello world", $dummy->get("existent"));
    }

    public function test_dump()
    {
        $dummy = $this->getDummyAbstractMap();

        $dummy->set("A", 1);
        $dummy->set("B", 2);
        $dummy->set("C", 3);

        $this->assertEquals(["A"=>1, "B"=>2, "C"=>3], $dummy->dump());
    }

    public function test_merge()
    {
        $dummy = $this->getDummyAbstractMap();

        $dummy->set("A", 1);
        $this->assertEquals(["A"=>1], $dummy->dump());

        $dummy->merge(["B"=>2, "C"=>3]);
        $this->assertEquals(["A"=>1, "B"=>2, "C"=>3], $dummy->dump());

        $dummy->merge(["A"=>5]);
        $this->assertEquals(["A"=>5, "B"=>2, "C"=>3], $dummy->dump());
    }
}