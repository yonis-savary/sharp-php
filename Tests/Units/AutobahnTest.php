<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\Http\Request;
use Sharp\Classes\Data\Database;
use Sharp\Classes\Extras\Autobahn;
use Sharp\Classes\Web\Router;
use Sharp\Tests\Models\TestUserData;

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
        list($autobahn, $router) = $this->getNewAutobahn();
        $autobahn->all(TestUserData::class);
        $this->assertCount(4, $router->getRoutes());

        $this->resetTestUserDataTable();
    }

    public function test_create()
    {
        list($autobahn, $router) = $this->getNewAutobahn();
        $autobahn->create(TestUserData::class);
        $this->assertCount(1, $router->getRoutes());

        $this->resetTestUserDataTable();

        $router->route(
            new Request("POST", "/test_user_data", [], ["fk_user" => 1, "data" => "NEW!"])
        );

        $this->assertTableCount(4);
    }

    public function test_read()
    {
        list($autobahn, $router) = $this->getNewAutobahn();
        $autobahn->read(TestUserData::class);
        $this->assertCount(1, $router->getRoutes());

        $res = $router->route(new Request("GET", "/test_user_data"));
        $this->assertCount(4, $res->getContent());

        $res = $router->route(new Request("GET", "/test_user_data", ["data" => "B"]));
        $this->assertCount(1, $res->getContent());

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
    }

    public function test_update()
    {
        $this->resetTestUserDataTable();

        list($autobahn, $router) = $this->getNewAutobahn();
        $db = Database::getInstance();

        $autobahn->update(TestUserData::class);
        $this->assertCount(1, $router->getRoutes());

        $router->route(new Request("PUT", "/test_user_data", ["id" => 1, "data" => "Y"]));
        $this->assertEquals("Y", $db->query("SELECT data FROM test_user_data WHERE id = 1")[0]["data"]);

        $router->route(new Request("PUT", "/test_user_data", ["id" => 1, "data" => "Z"]));
        $this->assertEquals("Z", $db->query("SELECT data FROM test_user_data WHERE id = 1")[0]["data"]);
    }

    public function test_delete()
    {
        $this->resetTestUserDataTable();

        list($autobahn, $router) = $this->getNewAutobahn();
        $autobahn->delete(TestUserData::class);
        $this->assertCount(1, $router->getRoutes());

        $router->route(new Request("DELETE", "/test_user_data", ["id" => 1]));
        $this->assertTableCount(2);

        # Dangerous query prevention
        $router->route(new Request("DELETE", "/test_user_data"));
        $this->assertTableCount(2);
    }

}