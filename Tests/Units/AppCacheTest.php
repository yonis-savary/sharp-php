<?php 

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Tests\Classes\AppCacheA;
use Sharp\Tests\Classes\AppCacheB;

class AppCacheTest extends TestCase
{
    public function test_collision()
    {
        $a = AppCacheA::get();
        $b = AppCacheB::get();

        $a->set("key", "abc");
        $b->set("key", "123");


        $this->assertEquals("abc", $a->get("key"));
        $this->assertEquals("123", $b->get("key"));
    }

    public function test_reference()
    {
        $first = AppCacheA::get();
        $second = AppCacheA::get();


        $first->set("refTest", "Hello!");

        $this->assertEquals("Hello!", $second->get("refTest"));
    }
}