<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\Env\Session;
use Sharp\Classes\Http\Request;
use Sharp\Classes\Security\Csrf;

class CsrfTest extends TestCase
{
    protected function getSessionMock(): Session
    {
        $class = new class extends Session {
            public function __construct()
            {
                $this->storage = [];
            }
        };

        return new $class();
    }

    protected function getDummyCsrf(): Csrf
    {
        return new Csrf($this->getSessionMock());
    }

    public function test_getHTMLInput()
    {
        $csrf = $this->getDummyCsrf();

        $input = $csrf->getHTMLInput();

        $this->assertMatchesRegularExpression(
            "/^<input .*value='[0-9a-f]+'.*>$/",
            $input
        );
    }

    public function test_getToken()
    {
        $csrf = $this->getDummyCsrf();

        $token = $csrf->getToken();

        $this->assertIsString($token);
        $this->assertMatchesRegularExpression("/^[0-9a-f]+$/", $token);
    }

    public function test_resetToken()
    {
        $csrf = $this->getDummyCsrf();

        $firstToken = $csrf->getToken();
        $csrf->resetToken();
        $secondToken = $csrf->getToken();

        $this->assertNotEquals($firstToken, $secondToken);
    }

    public function test_checkRequest()
    {
        $csrf = $this->getDummyCsrf();

        $inputName = $csrf->getConfiguration()["html-input-name"];

        $validToken = $csrf->getToken();
        $invalidToken = "nothing!";

        $this->assertTrue ($csrf->checkRequest(new Request("GET", "/", [$inputName => $validToken])));
        $this->assertFalse($csrf->checkRequest(new Request("GET", "/", [$inputName => $invalidToken])));
        $this->assertFalse($csrf->checkRequest(new Request("GET", "/")));
    }
}