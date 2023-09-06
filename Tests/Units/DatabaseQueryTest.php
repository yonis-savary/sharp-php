<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\Data\Classes\QueryField;
use Sharp\Classes\Data\Database;
use Sharp\Classes\Data\DatabaseQuery;
use Sharp\Tests\Models\User;
use Sharp\Tests\Models\UserData;

class DatabaseQueryTest extends TestCase
{
    protected function assertBuiltQueryContains(DatabaseQuery $query, string $needle)
    {
        $built = preg_replace("/\s{2,}/", " ", str_replace("\n", " ", $query->build()));
        $this->assertStringContainsString($needle, $built);
    }

    protected function assertBuiltQueryNotContains(DatabaseQuery $query, string $needle)
    {
        $built = preg_replace("/\s{2,}/", " ", str_replace("\n", " ", $query->build()));
        $this->assertStringNotContainsString($needle, $built);
    }

    public function test_set()
    {
        $q = new DatabaseQuery("dummy", DatabaseQuery::UPDATE);
        $q->set("field", 5);

        $this->assertBuiltQueryContains($q, "`field` = '5'");
    }

    public function test_setInsertField()
    {
        $q = new DatabaseQuery("dummy", DatabaseQuery::INSERT);
        $q->setInsertField(["A", "B", "C"]);

        $this->assertBuiltQueryContains($q, "(A,B,C)");
    }

    public function test_insertValues()
    {
        $q = new DatabaseQuery("dummy", DatabaseQuery::INSERT);
        $q->setInsertField(["A", "B", "C"]);
        $q->insertValues([1,2,3]);


        $this->assertBuiltQueryContains($q, "('1','2','3')");
    }

    public function test_addField()
    {
        $q = new DatabaseQuery("dummy", DatabaseQuery::SELECT);
        $q->addField("dummy", "id");

        $this->assertBuiltQueryContains($q, "`dummy`.id");
    }

    public function test_exploreModel()
    {
        $q = new DatabaseQuery("user_data", DatabaseQuery::SELECT);
        $q->exploreModel(UserData::class);
        $this->assertBuiltQueryContains($q, "`user_data`.fk_user");
        $this->assertBuiltQueryContains($q, "`user_data`.data");
        $this->assertBuiltQueryContains($q, "`user_data&fk_user`.id");
        $this->assertBuiltQueryContains($q, "`user_data&fk_user`.login");
        $this->assertBuiltQueryContains($q, "`user_data&fk_user`.password");

        $q = new DatabaseQuery("user_data", DatabaseQuery::SELECT);
        $q->exploreModel(UserData::class, false);
        $this->assertBuiltQueryContains($q, "`user_data`.fk_user");
        $this->assertBuiltQueryContains($q, "`user_data`.data");
        $this->assertBuiltQueryNotContains($q, "`user_data&fk_user`.id");
        $this->assertBuiltQueryNotContains($q, "`user_data&fk_user`.login");
        $this->assertBuiltQueryNotContains($q, "`user_data&fk_user`.password");

        $q = new DatabaseQuery("user_data", DatabaseQuery::SELECT);
        $q->exploreModel(UserData::class, true, ["user_data&fk_user"]);
        $this->assertBuiltQueryContains($q, "`user_data`.fk_user");
        $this->assertBuiltQueryContains($q, "`user_data`.data");
        $this->assertBuiltQueryNotContains($q, "`user_data&fk_user`.id");
        $this->assertBuiltQueryNotContains($q, "`user_data&fk_user`.login");
        $this->assertBuiltQueryNotContains($q, "`user_data&fk_user`.password");
    }


    public function test_limit()
    {
        $q = new DatabaseQuery("dummy", DatabaseQuery::SELECT);
        $q->limit(500);
        $this->assertBuiltQueryContains($q, "LIMIT 500");
        $this->assertBuiltQueryNotContains($q, "OFFSET");

        $q = new DatabaseQuery("dummy", DatabaseQuery::SELECT);
        $q->limit(500, 100);
        $this->assertBuiltQueryContains($q, "LIMIT 500 OFFSET 100");
    }

    public function test_offset()
    {
        $q = new DatabaseQuery("dummy", DatabaseQuery::SELECT);
        $q->limit(500);
        $q->offset(100);

        $this->assertBuiltQueryContains($q, "LIMIT 500 OFFSET 100");

        # Offset without query test
        $q = new DatabaseQuery("dummy", DatabaseQuery::SELECT);
        $q->offset(100);
        $this->assertBuiltQueryNotContains($q, "OFFSET 100");
    }

    public function test_where()
    {
        $q = (new DatabaseQuery("dummy", DatabaseQuery::SELECT))->where("id", 5);
        $this->assertBuiltQueryContains($q, "id = '5'");

        $q = (new DatabaseQuery("dummy", DatabaseQuery::SELECT))->where("id", 5, '>');
        $this->assertBuiltQueryContains($q, "id > '5'");

        $q = (new DatabaseQuery("dummy", DatabaseQuery::SELECT))->where("id", 5, '=', 'dummy');
        $this->assertBuiltQueryContains($q, "`dummy`.id = '5'");
    }

    public function test_whereSQL()
    {
        $q = new DatabaseQuery("dummy", DatabaseQuery::SELECT);
        $q->whereSQL("roses = 'Red'");

        $this->assertBuiltQueryContains($q, "(roses = 'Red')");

        $q = new DatabaseQuery("dummy", DatabaseQuery::SELECT);
        $q->whereSQL("roses = 'Red'");
        $q->whereSQL("violets = 'Blue'");

        $this->assertBuiltQueryContains($q, "(roses = 'Red') AND (violets = 'Blue')");

        $q = new DatabaseQuery("dummy", DatabaseQuery::SELECT);
        $q->whereSQL("roses = 'Red'");
        $q->where("violets", "Blue");

        $this->assertBuiltQueryContains($q, "(roses = 'Red') AND (violets = 'Blue')");
    }


    public function test_join()
    {
        $q = new DatabaseQuery("dummy", DatabaseQuery::SELECT);

        $q->join("LEFT", new QueryField("source", "field"), "=", "target", "targetAlias", "targetField");

        $this->assertBuiltQueryContains($q, "LEFT JOIN `target` AS `targetAlias` ON `source`.field = `targetAlias`.targetField");
    }

    public function test_order()
    {
        $q = new DatabaseQuery("dummy", DatabaseQuery::SELECT);
        $q->order("dummy", "field");
        $this->assertBuiltQueryContains($q, "ORDER BY `dummy`.field ASC");


        $q = new DatabaseQuery("dummy", DatabaseQuery::SELECT);
        $q->order("dummy", "field");
        $q->order("dummy", "id", "DESC");
        $this->assertBuiltQueryContains($q, "ORDER BY `dummy`.field ASC, `dummy`.id DESC");
    }


    public function test_build()
    {
        $q = new DatabaseQuery("dummy", DatabaseQuery::CREATE);
        $this->assertBuiltQueryContains($q, "INSERT INTO");

        $q = new DatabaseQuery("dummy", DatabaseQuery::READ);
        $this->assertBuiltQueryContains($q, "SELECT FROM");

        $q = new DatabaseQuery("dummy", DatabaseQuery::UPDATE);
        $this->assertBuiltQueryContains($q, "UPDATE");

        $q = new DatabaseQuery("dummy", DatabaseQuery::DELETE);
        $this->assertBuiltQueryContains($q, "DELETE FROM");

    }

    public function test_first()
    {
        $q = new DatabaseQuery("user_data", DatabaseQuery::SELECT);
        $q->exploreModel(UserData::class);

        $this->assertIsArray($q->first());

        $q->where("id", -1);
        $this->assertNull($q->first());
    }

    public function test_fetch()
    {
        $q = new DatabaseQuery("user_data", DatabaseQuery::SELECT);
        $q->exploreModel(UserData::class);

        $this->assertCount(
            Database::getInstance()->query("SELECT COUNT(*) as max FROM user_data")[0]["max"],
            $q->fetch()
        );

        $q->where("id", -1);
        $this->assertCount(0, $q->fetch());

        $q = new DatabaseQuery("user", DatabaseQuery::UPDATE);
        $q->set("login", "blah");
        $this->assertEquals(1, $q->fetch());

        // Set back the edited login
        User::update()->set("login", "admin")->fetch();
    }
}