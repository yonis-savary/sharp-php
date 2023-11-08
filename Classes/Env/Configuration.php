<?php

namespace Sharp\Classes\Env;

use Exception;
use Sharp\Classes\Core\AbstractMap;
use Sharp\Classes\Core\Component;
use Sharp\Core\Utils;

class Configuration extends AbstractMap
{
    use Component;

    protected ?string $filename = null;

    public static function getDefaultInstance()
    {
        return new self("sharp.json");
    }

    /**
     * @param string $filename `null` if the config is only an object, a relative path if it must be saved
     */
    public function __construct(string $filename=null)
    {
        if (!$filename)
            return;

        $filename = $this->filename = Utils::relativePath($filename);

        // Info: this verification is after the previous assignment
        // because we can create a config from nothing then save it in a file

        if (!is_file($filename))
            return;

        $json = file_get_contents($filename);
        $this->storage = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * @param string $path This parameter can be used as a "Save As..." feature to copy a configuration, if `null`, the current path is used
     */
    public function save(string $path=null): void
    {
        $path ??= $this->filename;

        if (!$path)
            throw new Exception("Couldn't save a configuration without a file name !");

        file_put_contents($path, json_encode($this->storage, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    }
}