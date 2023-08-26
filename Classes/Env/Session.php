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
        return new self($_SESSION, Storage::getInstance()->path("Sharp/Sessions"));
    }

    public function __construct(array &$storage=null, string $savePath=null)
    {
        parent::__construct($storage);

        $options = [];

        if (session_status() === PHP_SESSION_DISABLED)
            throw new Exception("Cannot use Session when sessions are disabled !");

        $options = [];
        if ($savePath)
            $options["save_path"] = $savePath;

        if (session_status() !== PHP_SESSION_ACTIVE)
            session_start($options);
    }
}