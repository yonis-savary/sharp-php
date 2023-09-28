<?php

namespace Sharp\Classes\Env;

use RuntimeException;
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
        $cacheStorage = Storage::getInstance()->getNewStorage("Cache");
        return new self($cacheStorage);
    }

    public function __construct(Storage $storage)
    {
        $storage->assertIsWritable();
        $this->storage = $storage;

        foreach ($this->storage->listFiles() as $file)
        {
            if (!($element = CacheElement::fromFile($file)))
                continue;

            $key = $element->key;
            if ($this->has($key))
                throw new RuntimeException("Duplicate key in cache directory [$key]");

            $this->index[$key] = $element;
        }
    }

    public function __destruct()
    {
        foreach ($this->index as $key => $object)
            $object->save($this->storage);
    }

    /**
     * Get the Cache directory Storage object
     */
    public function getStorage(): Storage
    {
        return $this->storage;
    }

    /**
     * @return array<string> Return existing keys in the cache
     */
    public function getKeys(): array
    {
        return array_keys($this->index);
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
     * Get a cache element content that you can edit directly
     *
     * @param mixed $defaultStarted default value to put in cache in case the key does not exists
     * @note don't forget to put '&' before calling this method
     * @example NULL `$ref = &$cache->getReference("key")`
     */
    public function &getReference(string $key, mixed $defaultStarter=[]): mixed
    {
        if (!$this->has($key))
            $this->set($key, $defaultStarter);

        return $this->index[$key]->getReference();
    }

    /**
     * Alias to `get($key, false)`, can be used
     * in assignement-conditions for better readability
     *
     * @return mixed Key value, `false` on failure
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
        $this->index[$key] ??= new CacheElement($key);
        $this->index[$key]->setContent($content, $timeToLive);
    }

    /**
     * Delete the object from the cache (useful for persitent objects)
     *
     * @param string $key Key to unset
     */
    public function delete(string $key): void
    {
        if (!$this->has($key))
            return;

        $this->index[$key]->delete();
        unset($this->index[$key]);
    }

    /**
     * Get a new Cache instance from a subdirectory created by the parent Cache
     */
    public function getSubCache(string $name): self
    {
        return new self($this->storage->getNewStorage($name));
    }
}