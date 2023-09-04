<?php

namespace Sharp\Tests\Classes;

use Sharp\Classes\Core\Configurable;

class DummyConfigurable
{
    use Configurable;

    public static function getDefaultConfiguration()
    {
        return [
            "enabled" => true,
            "cached" => false
        ];
    }
}