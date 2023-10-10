<?php

namespace Sharp\Classes\Env;

use Exception;
use RuntimeException;
use Sharp\Classes\Core\AbstractMap;
use Sharp\Classes\Core\Component;

class Session extends AbstractMap
{
    use Component;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_DISABLED)
            throw new Exception("Cannot use Session when sessions are disabled !");

        if (session_status() === PHP_SESSION_NONE)
        {
            $storage = Storage::getInstance()->getSubStorage("Sharp/Sessions");
            $storage->assertIsWritable();

            if (!session_start(["save_path" => $storage->getRoot()]))
                throw new RuntimeException("Cannot start session !");
        }

        $this->storage = &$_SESSION;
    }
}