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
        if (session_status() === PHP_SESSION_DISABLED)
            throw new Exception("Cannot use Session when sessions are disabled !");

        if (session_status() === PHP_SESSION_NONE)
        {
            $storage = Storage::getInstance()->getNewStorage("Sharp/Sessions");
            $storage->assertIsWritable();
            session_start(["save_path" => $storage->getRoot()]);
        }

        return new self();
    }

    public function __construct()
    {
        $this->storage = &$_SESSION;
    }
}