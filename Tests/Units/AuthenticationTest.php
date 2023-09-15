<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\Core\Events;
use Sharp\Classes\Security\Authentication;
use Sharp\Tests\Models\TestUser;

class AuthenticationTest extends TestCase
{
    public function test_attempt()
    {
        $authentication = new Authentication();

        // Good creds
        $this->assertTrue($authentication->attempt("admin", "admin"));

        // Bad creds/password
        $this->assertFalse($authentication->attempt("root", "admin"));
        $this->assertFalse($authentication->attempt("admin", "root"));
        $this->assertFalse($authentication->attempt("root", "root"));
        $this->assertFalse($authentication->attempt("root", "pleaseUseAGoodPassword"));

        $this->assertFalse($authentication->attempt("'); DELETE FROM user; --", "root"));
        $this->assertFalse($authentication->attempt("root", "'); DELETE FROM user; --"));

        $this->assertCount(
            1,
            TestUser::select()->fetch()
        );
    }

    public function test_logout()
    {
        $authentication = new Authentication();

        $authentication->attempt("admin", "admin");
        $this->assertTrue($authentication->isLogged());

        $authentication->logout();
        $this->assertFalse($authentication->isLogged());
    }

    public function test_event()
    {
        $events = Events::getInstance();
        $authentication = new Authentication();

        $eventVar = null;
        $events->on("authenticatedUser", function($event) use (&$eventVar) {
            $eventVar = $event["user"]["data"]["login"];
        });

        $authentication->attempt("admin", "root");
        $this->assertNull($eventVar);

        $authentication->attempt("admin", "admin");
        $this->assertEquals("admin", $eventVar);
    }


    public function test_isLogged()
    {
        $authentication = new Authentication();

        $authentication->attempt("admin", "admin");
        $this->assertTrue($authentication->isLogged());

        $authentication->attempt("root", "pleaseUseAGoodPassword");
        $this->assertFalse($authentication->isLogged());

        $authentication->attempt("admin", "admin");
        $this->assertTrue($authentication->isLogged());

        $authentication->logout();
        $this->assertFalse($authentication->isLogged());
    }

    public function test_attemptNumber()
    {
        $authentication = new Authentication();

        $authentication->attempt("admin", "admin");
        $this->assertEquals(0, $authentication->attemptNumber());

        $authentication->attempt("root", "pleaseUseAGoodPassword");
        $authentication->attempt("root", "pleaseUseAGoodPassword");
        $authentication->attempt("root", "pleaseUseAGoodPassword");
        $this->assertEquals(3, $authentication->attemptNumber());
    }

    public function test_getTestUser()
    {
        $authentication = new Authentication();

        $authentication->attempt("admin", "admin");
        $this->assertEquals([
            "data" => [
                "id" => 1,
                "login" => "admin",
                "password" => '$2y$08$pxfA4LlzVyXRPYVZH7czvu.gQQ8BNfzRdhejln2dwB7Bv6QafwAua',
                "salt" => "dummySalt"
            ]
        ], $authentication->getUser());

        $authentication->attempt("root", "root");
        $this->assertNull($authentication->getUser());
    }
}