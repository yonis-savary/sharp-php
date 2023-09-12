<?php

namespace Sharp\Tests\Units;

use PDO;
use PHPUnit\Framework\TestCase;
use Sharp\Classes\Data\Database;

class DatabaseTest extends TestCase
{
    public function test_getConnection()
    {
        $this->assertInstanceOf(
            PDO::class,
            Database::getInstance()->getConnection()
        );
    }

    public function test_isConnected()
    {
        $this->assertTrue(
            Database::getInstance()->isConnected()
        );
    }

    public function test_getDSN()
    {
        $this->assertIsString(
            Database::getInstance()->getDSN()
        );
    }

    public function test_quote()
    {
        $db = Database::getInstance();
        $this->assertEquals("'5'", $db->quote(5));
        $this->assertEquals("'5'", $db->quote('5'));
    }

    public function test_lastInsertId()
    {
        $db = Database::getInstance();
        $db->query("DELETE FROM sqlite_sequence WHERE name = 'test_user_data'");
        $db->query("DELETE FROM test_user_data");

        $db->query("INSERT INTO test_user_data (fk_user, data) VALUES ({}, {})", [1, 'next_id_test']);
        $this->assertEquals(1, $db->lastInsertId());
    }

    public function test_build()
    {
        $db = Database::getInstance();
        $this->assertEquals("SELECT '1'", $db->build("SELECT {}", [1]));
        $this->assertEquals("SELECT '1'", $db->build("SELECT {}", ['1']));
        $this->assertEquals("SELECT '1'", $db->build("SELECT '{}'", [1]));
        $this->assertEquals("SELECT '1'", $db->build("SELECT '{}'", ['1']));

        $this->assertEquals("SELECT ('1','2','3')", $db->build("SELECT {}", [[1,2,3]]));

    }

    public function test_query()
    {
        $db = Database::getInstance();

        $this->assertEquals(
            [[
                    "id" => 1,
                    "login" => "admin",
                    "password" => '$2y$08$pxfA4LlzVyXRPYVZH7czvu.gQQ8BNfzRdhejln2dwB7Bv6QafwAua',
                    "salt" => "dummySalt"
            ]],
            $db->query("SELECT * FROM test_user")
        );
    }

    public function test_hasTable()
    {
        $db = Database::getInstance();
        $this->assertTrue($db->hasTable("test_user"));
        $this->assertTrue($db->hasTable("test_user_data"));
        $this->assertFalse($db->hasTable("test_user_favorite"));
    }

    public function test_hasField()
    {
        $db = Database::getInstance();
        $this->assertTrue($db->hasField("test_user", "id"));
        $this->assertFalse($db->hasField("test_user", "inexistant"));
        $this->assertTrue($db->hasField("test_user_data", "fk_user"));
        $this->assertFalse($db->hasField("test_user_data", "inexistant"));
        $this->assertFalse($db->hasField("test_user_favorite", "id"));
    }
}