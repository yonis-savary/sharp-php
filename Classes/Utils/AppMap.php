<?php 

namespace Sharp\Classes\Utils;

use Sharp\Classes\Core\AbstractMap;
use Sharp\Classes\Env\Storage;

trait AppMap
{
    protected static ?AbstractMap $instance = null;

    final protected static function getHashName(): string
    {
        return md5(get_called_class());
    }

    final protected static function getAppMapsStorage(): Storage
    {
        return Storage::getInstance()->getSubStorage("Sharp/AppMaps");
    }

    public static function &get(): AbstractMap
    {
        if (self::$instance === null)
        {
            $hashName = self::getHashName();
            $storage = self::getAppMapsStorage();

            $data = [];
            if ($storage->isFile($hashName))
                $data = unserialize($storage->read($hashName));

            self::$instance = new AppMapInstance($hashName, $data);
        }
        
        return self::$instance;
    }
}