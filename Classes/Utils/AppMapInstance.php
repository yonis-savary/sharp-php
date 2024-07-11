<?php 

namespace Sharp\Classes\Utils;

use Sharp\Classes\Core\AbstractMap;
use Sharp\Classes\Env\Storage;

final class AppMapInstance extends AbstractMap {

    private string $hashName;

    public function __construct(string $hashName, $data)
    {
        $this->hashName = $hashName;
        $this->storage = $data;
    }

    public function __destruct()
    {
        Storage::getInstance()->getSubStorage("Sharp/AppMaps")
        ->write($this->hashName, serialize($this->storage));
    }
}