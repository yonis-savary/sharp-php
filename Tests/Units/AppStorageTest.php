<?php 

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Tests\Classes\AppStorageA;
use Sharp\Tests\Classes\AppStorageB;

class AppStorageTest extends TestCase
{
    public function test_collision()
    {
        $a = AppStorageA::get();
        $b = AppStorageB::get();

        $a->write("text.txt", "Hello");
        $b->write("text.txt", "Goodbye");

        $this->assertEquals("Hello", $a->read("text.txt"));
        $this->assertEquals("Goodbye", $b->read("text.txt"));
    }
}