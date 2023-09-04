<?php

namespace Sharp\Tests\Units;

use Exception;
use PHPUnit\Framework\TestCase;
use Sharp\Classes\Core\AbstractStorage;

class AbstractStorageTest extends TestCase
{
    protected function getDummyAbstractStorage(): AbstractStorage
    {
        $class = new class extends AbstractStorage {};
        return new $class();
    }

    public function test_get()
    {
        $dummy = $this->getDummyAbstractStorage();

        $this->assertEquals("A", $dummy->get("key", "A"));

        $dummy->set("key", "B");
        $this->assertEquals("B", $dummy->get("key", "A"));
    }

    public function test_try()
    {
        $dummy = $this->getDummyAbstractStorage();

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
        $dummy = $this->getDummyAbstractStorage();

        $this->assertEquals("A", $dummy->get("key", "A"));

        $dummy->set("key", "B");
        $this->assertEquals("B", $dummy->get("key", "A"));

        $dummy->set("key", 5);
        $this->assertEquals(5, $dummy->get("key", "A"));
    }

    public function test_has()
    {
        $dummy = $this->getDummyAbstractStorage();

        $this->assertFalse($dummy->has("key"));
        $dummy->set("key", "A");
        $this->assertTrue($dummy->has("key"));
    }

    public function test_unset()
    {
        $dummy = $this->getDummyAbstractStorage();

        $dummy->set("key", "A");
        $this->assertTrue($dummy->has("key"));
        $this->assertEquals("A", $dummy->get("key", "B"));

        $dummy->unset("key");
        $this->assertFalse($dummy->has("key"));
        $this->assertEquals("B", $dummy->get("key", "B"));
    }

    public function test_toArray()
    {
        $dummy = $this->getDummyAbstractStorage();

        $dummy->set("key", "A");
        $this->assertEquals(["A"], $dummy->toArray("key"));

        $dummy->set("key", [1, 2, 3]);
        $this->assertEquals([1, 2, 3], $dummy->toArray("key"));
    }
}