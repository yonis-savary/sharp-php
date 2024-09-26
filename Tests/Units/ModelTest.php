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

    public function test_column_format()
    {
        $user = TestUser::select()->where("id", 1)->first();

        $this->assertEquals(
            [
                "id" => 1,
                'login' => 'admin',
                'password' => '$2y$08$pxfA4LlzVyXRPYVZH7czvu.gQQ8BNfzRdhejln2dwB7Bv6QafwAua',
                'salt' => 'dummySalt',
                'blocked' => false
            ]
        , $user["data"]);
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

    public function test_findId()
    {
        $this->assertIsArray(TestUser::findId(1));
        $this->assertNull(TestUser::findId(1309809));
    }

    public function test_findWhere()
    {
        $this->assertIsArray(TestUser::findWhere(["id" => 1]));
        $this->assertNull(TestUser::findId(["id" => 1309809]));
    }

    public function test_updateId()
    {
        TestUser::updateId(1)->set("login", "testupdate")->fetch();
        $this->assertEquals("testupdate", TestUser::findId(1)["data"]["login"]);
    }

    public function test_updateRow()
    {
        TestUser::updateRow(1, [
            "login" => "testupdaterow"
        ]);
        $this->assertEquals("testupdaterow", TestUser::findId(1)["data"]["login"]);
    }

    public function test_deleteId()
    {
        TestUser::insertArray(["login" => "dummy", "password" => "any", "salt" => "any"]);
        $id = Database::getInstance()->lastInsertId();

        $this->assertIsArray(TestUser::findId($id));
        TestUser::deleteId($id);
        $this->assertNull(TestUser::findId($id));
    }

    public function test_selectWhere()
    {
        TestUserData::insertArray(["fk_user" => 1, "data" => "someTest"]);
        $this->assertCount(1, TestUserData::selectWhere(["data" => "someTest"]));

        TestUserData::insertArray(["fk_user" => 1, "data" => "someTest"]);
        $this->assertCount(2, TestUserData::selectWhere(["data" => "someTest"]));
    }

    public function test_existsWhere()
    {
        $insertedId = TestUserData::insertArray(["fk_user" => 1, "data" => "someExists"]);
        $this->assertTrue(TestUserData::existsWhere(["data" => "someExists"]));

        TestUserData::deleteId($insertedId);
        $this->assertFalse(TestUserData::existsWhere(["data" => "someExists"]));
    }

    public function test_idExists()
    {
        $insertedId = TestUserData::insertArray(["fk_user" => 1, "data" => "someIdExists"]);
        $this->assertTrue(TestUserData::idExists($insertedId));

        TestUserData::deleteId($insertedId);
        $this->assertFalse(TestUserData::idExists($insertedId));
    }

    public function test_deleteWhere()
    {
        $insertedId = TestUserData::insertArray(["fk_user" => 1, "data" => "someDelete"]);
        $this->assertTrue(TestUserData::idExists($insertedId));

        TestUserData::deleteWhere(["id" => $insertedId]);
        $this->assertFalse(TestUserData::idExists($insertedId));

        $insertedId = TestUserData::insertArray(["fk_user" => 1, "data" => "someDelete"]);
        $this->assertTrue(TestUserData::idExists($insertedId));

        TestUserData::deleteWhere(["data" => "someDelete"]);
        $this->assertFalse(TestUserData::idExists($insertedId));
    }
}