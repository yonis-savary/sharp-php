<?php

namespace Sharp\Classes\Env;

use Sharp\Classes\Core\Component;
use Sharp\Classes\Env\Classes\CacheElement;

/**
 * A cache is a directory that can make some object/data persistent
 */
class Cache
{
    const PERMANENT = 0;
    const SECOND    = 1;
    const MINUTE    = self::SECOND * 60;
    const HOUR      = self::MINUTE * 60;
    const DAY       = self::HOUR * 24;
    const WEEK      = self::DAY * 7;

    use Component;

    protected Storage $storage;

    /** @var array<string,CacheElement> Associative array with key => CacheElement */
    protected array $index = [];

    public static function getDefaultInstance()
    {
        return new self(Storage::getInstance()->getNewStorage("Cache"));
    }

    public function __construct(Storage $storage)
    {
        $storage->assertIsWritable();
        $this->storage = $storage;

        foreach ($this->storage->listFiles() as $file)
        {
            if ($element = CacheElement::fromFile($file))
                $this->index[$element->key] = $element;
        }
    }

    public function __destruct()
    {
        foreach (array_values($this->index) as $object)
            $object->save($this->storage);
    }

    /**
     * @param string $key Key to check the existance of
     * @return bool Is the key present in the cache
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->index);
    }

    /**
     * @param string $key Key to retrieve
     * @return mixed Object's content or `$default` is inexistant
     */
    public function get(string $key, mixed $default=null): mixed
    {
        if (!$this->has($key))
            return $default;

        return $this->index[$key]->getContent();
    }

    /**
     * Alias to `get($key, false)`, can be used
     * in assignement-conditions for better readability
     */
    public function try(string $key): mixed
    {
        return $this->get($key, false);
    }

    /**
     * Create/Overwrite an object to the cache
     * @param string $key Object identifier (unique)
     * @param mixed $content Object to store/serialize
     * @param int $timeToLive Object life time in seconds (set 0 for permanent)
     *
     * @note You can use `Cache::SECOND|MINUTE|HOUR|DAY|WEEK` constants to help your write a clean duration
     */
    public function set(string $key, mixed $content, int $timeToLive=3600*24)
    {
        if (!$this->has($key))
            $this->index[$key] = new CacheElement($key);

        $this->index[$key]->setContent($content, $timeToLive);
    }

    /**
     * Delete the object from the cache (useful for persitent objects)
     */
    public function delete(string $key): void
    {
        if (!$this->has($key))
            return;

        $this->index[$key]->delete();
        unset($this->index[$key]);
    }
}