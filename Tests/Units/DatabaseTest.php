<?php

namespace Sharp\Tests\Unit;

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
        $db->query("DELETE FROM sqlite_sequence WHERE name = 'user_data'");
        $db->query("DELETE FROM user_data");

        $db->query("INSERT INTO user_data (fk_user, data) VALUES ({}, {})", [1, 'next_id_test']);
        $this->assertEquals(1, $db->lastInsertId());
    }

    public function test_build()
    {
        $db = Database::getInstance();
        $this->assertEquals("SELECT '1'", $db->build("SELECT {}", [1]));
        $this->assertEquals("SELECT '1'", $db->build("SELECT {}", ['1']));
        $this->assertEquals("SELECT '1'", $db->build("SELECT '{}'", [1]));
        $this->assertEquals("SELECT '1'", $db->build("SELECT '{}'", ['1']));

    }

    public function test_query()
    {
        $db = Database::getInstance();

        $this->assertEquals(
            [
                [
                    "id" => 1,
                    "login" => "admin",
                    "password" => '$2y$08$t.zEvNyj78yxcX7ZycPjdO4hAVGiaOs92liqtzIoh8dPEFk5iX9hq',
                    "salt" => "dummySalt"
                ]
            ],
            $db->query("SELECT * FROM user")
        );
    }

    public function test_hasTable()
    {
        $db = Database::getInstance();
        $this->assertTrue($db->hasTable("user"));
        $this->assertTrue($db->hasTable("user_data"));
        $this->assertFalse($db->hasTable("user_favorite"));
    }

    public function test_hasField()
    {
        $db = Database::getInstance();
        $this->assertTrue($db->hasField("user", "id"));
        $this->assertFalse($db->hasField("user", "inexistant"));
        $this->assertTrue($db->hasField("user_data", "fk_user"));
        $this->assertFalse($db->hasField("user_data", "inexistant"));
        $this->assertFalse($db->hasField("user_favorite", "id"));
    }


}