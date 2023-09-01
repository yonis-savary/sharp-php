<?php

namespace Sharp\Classes\Env;

use Exception;
use Sharp\Classes\Core\Component;
use Sharp\Core\Utils;

class Config
{
    use Component;

    protected ?string $filename = null;
    protected array $content = [];

    public static function getDefaultInstance()
    {
        return new self(Utils::relativePath("sharp.json"));
    }

    public function __construct(string $filename=null)
    {
        if (!$filename)
            return;

        $this->filename = $filename;

        // Warning: this verification is after the previous assignement
        // because we can create a config from nothing then save it in a file

        if (!is_file($filename))
            return;

        $json = file_get_contents($filename);
        $this->content = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
    }

    public function set(string $key, mixed $value): void
    {
        $this->content[$key] = $value;
    }

    public function get(string $key, mixed $default=null): mixed
    {
        if (!array_key_exists($key, $this->content))
            return $default;
        return $this->content[$key];
    }

    public function toArray(string $key): array
    {
        return Utils::toArray($this->get($key, []));
    }

    public function try(string $key): mixed
    {
        return $this->get($key, false) ;
    }

    /**
     * @param string $path This parameter can be used as a "Save As..." feature to copy a configuration, if null, the current path is used
     */
    public function save(string $path=null): void
    {
        $path ??= $this->filename;

        if (!$path)
            throw new Exception("Couldn't save a config without file name !");

        file_put_contents($path, json_encode($this->content), JSON_THROW_ON_ERROR);
    }
}