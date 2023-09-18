<?php

namespace Sharp\Classes\Core;

use Sharp\Core\Utils;

/**
 * `AbstractMap` is a way to store `key-values` data
 * in a class, which got very simple methods like 'set', 'get', 'has'...
 */
abstract class AbstractMap
{
    protected array $storage = [];

    public function __construct(array &$storage=null)
    {
        $this->storage = $storage ?? [];
    }

    /**
     * Get a value from the storage
     *
     * @param string $key Key to retrieve
     * @param mixed $default Value to use if the key does not exists
     */
    final public function get(string $key, mixed $default=null): mixed
    {
        if (!array_key_exists($key, $this->storage))
            return $default;

        return $this->storage[$key];
    }

    /**
     * Get a value from the storage or return false if inexistant
     *
     * @param string $key Key to retrieve
     */
    final public function try(string $key): mixed
    {
        return $this->get($key, false);
    }

    /**
     * Set/Overwrite a value in the storage
     *
     * @param string $key Key to set/overwrite
     * @param mixed $value New value
     */
    final public function set(string $key, mixed $value): void
    {
        $this->storage[$key] = $value;
    }

    /**
     * Check if given keys exists all at the same time
     *
     * @param string ...$keys Keys to check the existance
     */
    final public function has(string ...$keys): bool
    {
        foreach ($keys as $key)
        {
            if (!array_key_exists($key, $this->storage))
                return false;
        }
        return true;
    }

    /**
     * Unset every given given keys from the storage
     *
     * @param string ...$keys Keys to unset
     */
    final public function unset(string ...$keys): void
    {
        foreach ($keys as $key)
        {
            if (array_key_exists($key, $this->storage))
                unset($this->storage[$key]);
        }
    }

    /**
     * Shortcut to `Utils::toArray($this->get())`
     *
     * Represent any key value as an array
     * (If the key is inexistant, return an empty array)
     */
    final public function toArray(string $key): array
    {
        return Utils::toArray($this->get($key, []));
    }
}