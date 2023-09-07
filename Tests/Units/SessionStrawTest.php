<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Tests\Classes\TestUserId;

class SessionStrawTest extends TestCase
{
    public function test_set()
    {
        TestUserId::unset();

        TestUserId::set(100);
        $this->assertEquals(100, TestUserId::get());

        TestUserId::set(50);
        $this->assertEquals(50, TestUserId::get());
    }

    public function test_get()
    {
        TestUserId::unset();
        $this->assertFalse(TestUserId::get());

        TestUserId::set(50);
        $this->assertEquals(50, TestUserId::get());

        TestUserId::unset();
        $this->assertFalse(TestUserId::get());
    }
}