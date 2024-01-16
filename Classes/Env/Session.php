<?php

namespace Sharp\Classes\Env;

use Exception;
use RuntimeException;
use Sharp\Classes\Core\AbstractMap;
use Sharp\Classes\Core\Component;
use Sharp\Core\Autoloader;

class Session extends AbstractMap
{
    use Component;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_DISABLED)
            throw new Exception("Cannot use Session when sessions are disabled !");

        if (session_status() === PHP_SESSION_NONE)
        {
            // Setting the session_name has two big advantages to it !
            // - Avoid sessions collision between two apps that are on different ports of the same host
            // - PHP Still clear session files (which is disabled if a custom session path is used)
            // This way, two applications that don't have the same root will have different sessions
            session_name(md5(Autoloader::projectRoot()));

            if (!session_start())
                throw new RuntimeException("Cannot start session !");
        }

        $this->storage = &$_SESSION;
    }
}