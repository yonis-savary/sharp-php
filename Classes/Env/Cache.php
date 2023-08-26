<?php

namespace Sharp\Classes\Env;

use Exception;
use Sharp\Classes\Core\Component;

class Cache
{
    use Component;

    protected Storage $storage;

    protected array $index = [];

    public static function getDefaultInstance()
    {
        return new self(Storage::getInstance(), "Cache");
    }

    public function __construct(Storage $storage, string $name)
    {
        $this->storage = $storage->getNewStorage($name);
        $this->analyseStorage();
    }

    public function __destruct()
    {
        foreach (array_values($this->index) as $object)
        {
            if (!array_key_exists("content", $object))
                continue;

            $this->storage->write(
                join("_", [$object["creationDate"], $object["timeToLive"], $object["key"]]),
                serialize($object["content"])
            );
        }
    }

    public function analyseStorage()
    {
        foreach ($this->storage->listFiles() as $file)
        {
            list($creationDate, $timeToLive, $key) = explode("_", basename($file), 3);

            $creationDate = intval($creationDate);
            $timeToLive = intval($timeToLive);

            if ($creationDate + $timeToLive <= time())
            {
                $this->storage->unlink($file);
                continue;
            }

            $this->index[$key] = [
                "creationDate" => $creationDate,
                "timeToLive" => $timeToLive,
                "key" => $key,
                "file" => $file
            ];
        }
    }

    public function get(string $key, mixed $default=null)
    {
        if (!array_key_exists($key, $this->index))
            return $default;

        $object = $this->index[$key];

        if (array_key_exists("content", $object))
            return $object["content"];

        if (array_key_exists("file", $object))
        {
            $object["content"] = unserialize(file_get_contents($object["file"]));
            $this->index[$key] = $object;

            return $object["content"];
        }

        throw new Exception("Invalid cache object !");
    }

    public function try(string $key)
    {
        return $this->get($key, false);
    }

    public function set(string $key, mixed $content, int $timeToLive=3600*24)
    {
        $this->index[$key] ??= ["key" => $key];
        $this->index[$key]["timeToLive"] = $timeToLive;
        $this->index[$key]["creationDate"] = time();
        $this->index[$key]["content"] = $content;
    }
}