<?php

namespace Sharp\Classes\Env;

use Exception;
use Sharp\Classes\Core\AbstractStorage;
use Sharp\Classes\Core\Component;

class Session extends AbstractStorage
{
    use Component;

    public static function getDefaultInstance()
    {
        if (PHP_SESSION_DISABLED)
            throw new Exception("Cannot use Session when sessions are disabled !");

        if (PHP_SESSION_NONE)
            session_start(["save_path" => Storage::getInstance()->path("Sharp/Sessions")]);

        return new self($_SESSION);
    }
}