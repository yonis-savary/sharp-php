<?php

namespace Sharp\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\Security\Auth;
use Sharp\Tests\Models\User;

class AuthTest extends TestCase
{
    public function test_attempt()
    {
        $auth = new Auth();

        // Good creds
        $this->assertTrue($auth->attempt("admin", "admin"));

        // Bad creds/password
        $this->assertFalse($auth->attempt("root", "admin"));
        $this->assertFalse($auth->attempt("admin", "root"));
        $this->assertFalse($auth->attempt("root", "root"));
        $this->assertFalse($auth->attempt("root", "pleaseUseAGoodPassword"));

        $this->assertFalse($auth->attempt("'); DELETE FROM user; --", "root"));
        $this->assertFalse($auth->attempt("root", "'); DELETE FROM user; --"));

        $this->assertCount(
            1,
            User::select()->fetch()
        );
    }

    public function test_logout()
    {
        $auth = new Auth();

        $auth->attempt("admin", "admin");
        $this->assertTrue($auth->isLogged());

        $auth->logout();
        $this->assertFalse($auth->isLogged());
    }

    public function test_isLogged()
    {
        $auth = new Auth();

        $auth->attempt("admin", "admin");
        $this->assertTrue($auth->isLogged());

        $auth->attempt("root", "pleaseUseAGoodPassword");
        $this->assertFalse($auth->isLogged());

        $auth->attempt("admin", "admin");
        $this->assertTrue($auth->isLogged());

        $auth->logout();
        $this->assertFalse($auth->isLogged());
    }

    public function test_attemptNumber()
    {
        $auth = new Auth();

        $auth->attempt("admin", "admin");
        $this->assertEquals(0, $auth->attemptNumber());

        $auth->attempt("root", "pleaseUseAGoodPassword");
        $auth->attempt("root", "pleaseUseAGoodPassword");
        $auth->attempt("root", "pleaseUseAGoodPassword");
        $this->assertEquals(3, $auth->attemptNumber());
    }

    public function test_getUser()
    {
        $auth = new Auth();

        $auth->attempt("admin", "admin");
        $this->assertEquals([
            "data" => [
                "id" => 1,
                "login" => "admin",
                "password" => '$2y$08$pxfA4LlzVyXRPYVZH7czvu.gQQ8BNfzRdhejln2dwB7Bv6QafwAua',
                "salt" => "dummySalt"
            ]
        ], $auth->getUser());

        $auth->attempt("root", "root");
        $this->assertNull($auth->getUser());
    }
}