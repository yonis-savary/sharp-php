<?php

namespace Sharp\Tests\Units;

use Exception;
use PHPUnit\Framework\TestCase;
use Sharp\Classes\Core\EventListener;
use Sharp\Classes\Http\Request;
use Sharp\Classes\Data\Database;
use Sharp\Classes\Extras\Autobahn;
use Sharp\Classes\Web\Router;
use Sharp\Tests\Models\TestUserData;
use Sharp\Classes\Events\AutobahnEvents\AutobahnCreateBefore;
use Sharp\Classes\Events\AutobahnEvents\AutobahnCreateAfter;
use Sharp\Classes\Events\AutobahnEvents\AutobahnMultipleCreateBefore;
use Sharp\Classes\Events\AutobahnEvents\AutobahnMultipleCreateAfter;
use Sharp\Classes\Events\AutobahnEvents\AutobahnReadBefore;
use Sharp\Classes\Events\AutobahnEvents\AutobahnReadAfter;
use Sharp\Classes\Events\AutobahnEvents\AutobahnUpdateBefore;
use Sharp\Classes\Events\AutobahnEvents\AutobahnUpdateAfter;
use Sharp\Classes\Events\AutobahnEvents\AutobahnDeleteBefore;
use Sharp\Classes\Events\AutobahnEvents\AutobahnDeleteAfter;

class AutobahnTest extends TestCase
{
    protected function getNewAutobahn()
    {
        $r = new Router;
        $a = new Autobahn($r);

        return [$a, $r];
    }

    protected function resetTestUserDataTable()
    {
        $db = Database::getInstance();
        $db->query("DELETE FROM test_user_data;");
        $db->query("INSERT INTO test_user_data (id, fk_user, data) VALUES (1, 1, 'A'), (2, 1, 'B'), (3, 1, 'C');");
        $db->query("UPDATE sqlite_sequence SET seq = 3 WHERE name = 'test_user_data'");

        $this->assertTableCount(3);
    }

    protected function assertTableCount(int $count, string $table="test_user_data")
    {
        $this->assertEquals(
            $count,
            Database::getInstance()->query("SELECT COUNT(*) as max FROM $table")[0]["max"]
        );
    }

    public function test_all()
    {
        $this->resetTestUserDataTable();

        list($autobahn, $router) = $this->getNewAutobahn();
        $autobahn->all(TestUserData::class);
        $this->assertCount(5, $router->getRoutes());

    }

    public function test_create()
    {
        $dispatchedBeforeEvent = false;
        $dispatchedAfterEvent = false;
        EventListener::getInstance()->on(AutobahnCreateBefore::class, function() use (&$dispatchedBeforeEvent, &$dispatchedAfterEvent) { $dispatchedBeforeEvent = (!$dispatchedAfterEvent); });
        EventListener::getInstance()->on(AutobahnCreateAfter::class, function() use (&$dispatchedBeforeEvent, &$dispatchedAfterEvent) { $dispatchedAfterEvent = $dispatchedBeforeEvent; });

        $this->resetTestUserDataTable();

        list($autobahn, $router) = $this->getNewAutobahn();
        $autobahn->create(TestUserData::class);
        $this->assertCount(2, $router->getRoutes());

        $nextId = Database::getInstance()->query("SELECT MAX(id)+1 as next FROM test_user_data")[0]["next"];

        $response = $router->route(
            new Request("POST", "/test_user_data", [], ["fk_user" => 1, "data" => "NEW!"])
        );

        $this->assertEquals($nextId, $response->getContent()["insertedId"]);

        $this->assertTableCount(4);

        $this->assertTrue($dispatchedBeforeEvent);
        $this->assertTrue($dispatchedAfterEvent);
    }

    public function test_multipleCreate()
    {
        $dispatchedBeforeEvent = false;
        $dispatchedAfterEvent = false;
        EventListener::getInstance()->on(AutobahnMultipleCreateBefore::class, function() use (&$dispatchedBeforeEvent, &$dispatchedAfterEvent) { $dispatchedBeforeEvent = (!$dispatchedAfterEvent); });
        EventListener::getInstance()->on(AutobahnMultipleCreateAfter::class, function() use (&$dispatchedBeforeEvent, &$dispatchedAfterEvent) { $dispatchedAfterEvent = $dispatchedBeforeEvent; });

        $this->resetTestUserDataTable();

        list($autobahn, $router) = $this->getNewAutobahn();
        $autobahn->create(TestUserData::class);
        $this->assertCount(2, $router->getRoutes());

        $response = $router->route(
            new Request("POST", "/test_user_data/create-multiples", body: [
                ["fk_user" => 1, "data" => "NEW A !"],
                ["fk_user" => 1, "data" => "NEW B !"],
                ["fk_user" => 1, "data" => "NEW C !"],
            ])
        );

        $insertedIds = $response->getContent()["insertedId"];

        $this->assertEquals([4,5,6], $insertedIds);

        $this->expectException(Exception::class);
        $response = $router->route(
            new Request("POST", "/test_user_data/create-multiples", body: [
                ["fk_user" => 1, "data" => "NEW A !"],
                ["data" => "NEW B !"],
            ])
        );

        $this->assertTrue($dispatchedBeforeEvent);
        $this->assertTrue($dispatchedAfterEvent);
    }


    public function test_read()
    {
        $dispatchedBeforeEvent = false;
        $dispatchedAfterEvent = false;
        EventListener::getInstance()->on(AutobahnReadBefore::class, function() use (&$dispatchedBeforeEvent, &$dispatchedAfterEvent) { $dispatchedBeforeEvent = (!$dispatchedAfterEvent); });
        EventListener::getInstance()->on(AutobahnReadAfter::class, function() use (&$dispatchedBeforeEvent, &$dispatchedAfterEvent) { $dispatchedAfterEvent = $dispatchedBeforeEvent; });

        $this->resetTestUserDataTable();

        list($autobahn, $router) = $this->getNewAutobahn();
        $autobahn->read(TestUserData::class);
        $this->assertCount(1, $router->getRoutes());

        $res = $router->route(new Request("GET", "/test_user_data"));
        $this->assertCount(3, $res->getContent());

        $res = $router->route(new Request("GET", "/test_user_data", ["data" => "B"]));
        $this->assertCount(1, $res->getContent());

        $res = $router->route(new Request("GET", "/test_user_data", ["data" => ["A", "B"]]));
        $this->assertCount(2, $res->getContent());

        /*

        Expected format : {
            data: {
                id: _,
                fk_user: _,
                data: _,
            }
            fk_user: {
                data: {
                    id: _
                    login: _
                    password: _
                    salt: _
                }
            }
        }
        */

        $data = $res->getContent()[0];
        $this->assertArrayHasKey("data", $data);
        $this->assertArrayHasKey("data", $data["data"]);
        $this->assertArrayHasKey("fk_user", $data);
        $this->assertArrayHasKey("data", $data["fk_user"]);
        $this->assertArrayHasKey("id", $data["fk_user"]["data"]);

        $res = $router->route(new Request("GET", "/test_user_data", ["data" => "B", "_ignores" => ["test_user_data&fk_user"]]));
        $this->assertCount(1, $res->getContent());

        $data = $res->getContent()[0];
        $this->assertArrayHasKey("data", $data);
        $this->assertArrayHasKey("data", $data["data"]);
        $this->assertArrayNotHasKey("fk_user", $data);

        $res = $router->route(new Request("GET", "/test_user_data", ["_join" => false]));
        $this->assertCount(3, $res->getContent());
        $data = $res->getContent()[0];
        $this->assertArrayHasKey("data", $data);
        $this->assertArrayHasKey("data", $data["data"]);
        $this->assertArrayNotHasKey("fk_user", $data);

        $this->assertTrue($dispatchedBeforeEvent);
        $this->assertTrue($dispatchedAfterEvent);
    }

    public function test_update()
    {
        $dispatchedBeforeEvent = false;
        $dispatchedAfterEvent = false;
        EventListener::getInstance()->on(AutobahnUpdateBefore::class, function() use (&$dispatchedBeforeEvent, &$dispatchedAfterEvent) { $dispatchedBeforeEvent = (!$dispatchedAfterEvent); });
        EventListener::getInstance()->on(AutobahnUpdateAfter::class, function() use (&$dispatchedBeforeEvent, &$dispatchedAfterEvent) { $dispatchedAfterEvent = $dispatchedBeforeEvent; });

        $this->resetTestUserDataTable();

        list($autobahn, $router) = $this->getNewAutobahn();
        $db = Database::getInstance();

        $autobahn->update(TestUserData::class);
        $this->assertCount(1, $router->getRoutes());

        $router->route(new Request("PUT", "/test_user_data", ["id" => 1, "data" => "Y"]));
        $this->assertEquals("Y", $db->query("SELECT data FROM test_user_data WHERE id = 1")[0]["data"]);

        $router->route(new Request("PUT", "/test_user_data", ["id" => 1, "data" => "Z"]));
        $this->assertEquals("Z", $db->query("SELECT data FROM test_user_data WHERE id = 1")[0]["data"]);

        $this->resetTestUserDataTable();

        $router->route(new Request("PUT", "/test_user_data", ["id" => [1,2], "data" => "Z"]));
        $this->assertEquals("Z", $db->query("SELECT data FROM test_user_data WHERE id = 1")[0]["data"]);
        $this->assertEquals("Z", $db->query("SELECT data FROM test_user_data WHERE id = 2")[0]["data"]);

        $this->assertTrue($dispatchedBeforeEvent);
        $this->assertTrue($dispatchedAfterEvent);
    }

    public function test_delete()
    {
        $dispatchedBeforeEvent = false;
        $dispatchedAfterEvent = false;
        EventListener::getInstance()->on(AutobahnDeleteBefore::class, function() use (&$dispatchedBeforeEvent, &$dispatchedAfterEvent) { $dispatchedBeforeEvent = (!$dispatchedAfterEvent); });
        EventListener::getInstance()->on(AutobahnDeleteAfter::class, function() use (&$dispatchedBeforeEvent, &$dispatchedAfterEvent) { $dispatchedAfterEvent = $dispatchedBeforeEvent; });

        $this->resetTestUserDataTable();

        list($autobahn, $router) = $this->getNewAutobahn();
        $autobahn->delete(TestUserData::class);
        $this->assertCount(1, $router->getRoutes());

        $router->route(new Request("DELETE", "/test_user_data", ["id" => 1]));
        $this->assertTableCount(2);

        $this->assertTrue($dispatchedBeforeEvent);
        $this->assertTrue($dispatchedAfterEvent);

        # Dangerous query prevention
        $router->route(new Request("DELETE", "/test_user_data"));
        $this->assertTableCount(2);

        $this->resetTestUserDataTable();

        $router->route(new Request("DELETE", "/test_user_data", ["id" => [1,2]]));
        $this->assertTableCount(1);
    }

}