<?php

namespace Sharp\Tests\Units;

use Exception;
use PHPUnit\Framework\TestCase;
use Sharp\Classes\Data\Database;
use Sharp\Classes\Data\DatabaseQuery;
use Sharp\Classes\Data\Model;
use Sharp\Classes\Data\DatabaseField;
use Sharp\Tests\Models\TestUser;
use Sharp\Tests\Models\TestUserData;

class ModelTest extends TestCase
{
    public static function getSampleModel()
    {
        return new class
        {
            use Model;

            public static function getTable(): string
            {
                return "test_user";
            }

            public static function getPrimaryKey(): string
            {
                return "id";
            }

            public static function getFields(): array
            {
                return [
                    "id" => new DatabaseField("id", DatabaseField::INTEGER, false, [DatabaseField::IS_UNIQUE]),
                    "login" => new DatabaseField("id", DatabaseField::STRING, false),
                    "password" => new DatabaseField("id", DatabaseField::STRING, false)
                ];
            }

            public function validate(array $data=null): bool
            {
                $data ??= $this->toArray();

                if (!($data["id"] ?? null))
                    throw new Exception("[id] field is needed !");

                if (!($data["login"] ?? null))
                    throw new Exception("[login] field is needed !");

                if (!($data["password"] ?? null))
                    throw new Exception("[password] field is needed !");

                return true;
            }
        };
    }

    public function test_getFieldNames()
    {
        $user = self::getSampleModel();

        $this->assertEquals(
            ["id", "login", "password"],
            $user::getFieldNames()
        );
    }

    public function test___construct()
    {
        $user = self::getSampleModel();

        $admin = new $user([
            "id" => 1,
            "login" => "admin",
            "password" => password_hash("admin", PASSWORD_BCRYPT)
        ]);

        $this->assertEquals(1, $admin->id);
        $this->assertEquals("admin", $admin->login);
        $this->assertTrue(password_verify("admin", $admin->password));
    }

    public function test_insert()
    {
        $user = self::getSampleModel();
        $this->assertInstanceOf(DatabaseQuery::class, $user::insert());
    }

    public function test_select()
    {
        $user = self::getSampleModel();
        $this->assertInstanceOf(DatabaseQuery::class, $user::select());
    }

    public function test_update()
    {
        $user = self::getSampleModel();
        $this->assertInstanceOf(DatabaseQuery::class, $user::update());
    }

    public function test_delete()
    {
        $user = self::getSampleModel();
        $this->assertInstanceOf(DatabaseQuery::class, $user::delete());
    }

    public function test_toArray()
    {
        $user = self::getSampleModel();

        /** @var Model $admin */
        $admin = new $user([
            "id" => 1,
            "login" => "admin",
            "password" => "dummy"
        ]);

        $this->assertEquals([
            "id" => 1,
            "login" => "admin",
            "password" => "dummy"
        ], $admin->toArray());
    }

    public function test_validate()
    {
        $user = self::getSampleModel();

        $this->assertTrue((new $user(["id" => 1, "login" => "admin", "password" => "dummy"]))->validate());

        $this->expectException(Exception::class);
        (new $user(["login" => "admin", "password" => "dummy"]))->validate();

        $this->expectException(Exception::class);
        (new $user(["id" => 1, "password" => "dummy"]))->validate();

        $this->expectException(Exception::class);
        (new $user(["id" => 1, "login" => "admin"]))->validate();
    }

    public function test_insertArray()
    {
        $db = Database::getInstance();
        $nextId = $db->query("SELECT MAX(id) + 1 as next FROM test_user_data")[0]["next"];

        $inserted = TestUserData::insertArray([
            "fk_user" => 1,
            "data" => "someString"
        ]);

        $this->assertEquals($nextId, $inserted);
    }

    public function test_fromId()
    {
        $this->assertIsArray(TestUser::fromId(1));
        $this->assertNull(TestUser::fromId(1309809));
    }
}