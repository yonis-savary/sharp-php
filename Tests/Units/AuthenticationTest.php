<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\Core\EventListener;
use Sharp\Classes\Events\AuthenticatedUser;
use Sharp\Classes\Security\Authentication;
use Sharp\Tests\Models\TestUser;

class AuthenticationTest extends TestCase
{
    public function test_attempt()
    {
        $authentication = new Authentication();
        $authentication->logout();

        // Good credentials
        $this->assertTrue($authentication->attempt("admin", "admin"));

        // Bad credentials/password
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
        $authentication->logout();

        $authentication->attempt("admin", "admin");
        $this->assertTrue($authentication->isLogged());

        $authentication->logout();
        $this->assertFalse($authentication->isLogged());
    }

    public function test_event()
    {
        $events = EventListener::getInstance();
        $authentication = new Authentication();
        $authentication->logout();

        $eventVar = null;
        $events->on(AuthenticatedUser::class, function(AuthenticatedUser $event) use (&$eventVar) {
            $eventVar = $event->user["data"]["login"];
        });

        $authentication->attempt("admin", "root");
        $this->assertNull($eventVar);

        $authentication->attempt("admin", "admin");
        $this->assertEquals("admin", $eventVar);

        EventListener::removeInstance();
    }


    public function test_isLogged()
    {
        $authentication = new Authentication();
        $authentication->logout();

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
        $authentication->logout();

        $authentication->attempt("admin", "admin");
        $this->assertEquals(0, $authentication->attemptNumber());

        $authentication->attempt("root", "pleaseUseAGoodPassword");
        $authentication->attempt("root", "pleaseUseAGoodPassword");
        $authentication->attempt("root", "pleaseUseAGoodPassword");
        $this->assertEquals(3, $authentication->attemptNumber());
    }

    public function test_login()
    {
        $authentication = new Authentication();
        $authentication->logout();

        $this->assertFalse($authentication->isLogged());

        $authentication->login(["A" => 5]);
        $this->assertTrue($authentication->isLogged());

        $this->assertEquals(["A" => 5], $authentication->getUser());
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
                "salt" => "dummySalt",
                'blocked' => false
            ]
        ], $authentication->getUser());

        $authentication->attempt("root", "root");
        $this->assertNull($authentication->getUser());
    }
}