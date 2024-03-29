<?php

namespace Sharp\Classes\Core;

/**
 * Components are classes that has a global instance (like a singleton)
 * but that can also be instantiated
 *
 * When creating a component, you can implement `getDefaultInstance()`
 * which return the default instance when the singleton is initialized
 */
trait Component
{
    /**
     * @var ?static $instance
     */
    public static $instance = null;

    /**
     * This function must return an instance of the called class
     * it is automatically called when the singleton is called the first time
     */
    public static function getDefaultInstance(): static
    {
        return new self();
    }

    /**
     * Get the singleton instance of the called class
     * (Initialize a new instance with `getDefaultInstance()` if needed)
     */
    final public static function &getInstance(): static
    {
        if (!self::$instance)
            self::setInstance(self::getDefaultInstance());

        return self::$instance;
    }

    /**
     * Replace the singleton instance with a new one
     * If your class uses `Configurable` trait, its configuration is automatically loaded
     *
     * @param static $newInstance New instance to replace the current one
     */
    final public static function setInstance(self $newInstance): void
    {
        self::$instance = $newInstance;
    }

    /**
     * Destroy the current instance (And therefore call `__destruct()`)
     */
    final public static function removeInstance(): void
    {
        self::$instance = null;
    }
}