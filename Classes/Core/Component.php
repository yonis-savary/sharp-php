<?php

namespace Sharp\Classes\Core;

use Sharp\Core\Utils;

/**
 * Components are classes that has a global instance (like a singleton)
 * but that can also be instanciated
 *
 * When creating a component, you can implement `getDefaultInstance()`
 * which return the default instance when the singleton is initialized
 */
trait Component
{
    /** @var ?static $instance */
    public static $instance = null;

    /**
     * This function must return an instance of the called class
     * it is automatically called when the singleton is called the first time
     *
     * @return static Default singleton instance
     */
    public static function getDefaultInstance()
    {
        return new self();
    }

    /**
     * Get the singleton instance of the called class
     * (Initialize a new instance with `getDefaultInstance()` if needed)
     *
     * @return static Singleton instance
     */
    final public static function getInstance()
    {
        if (self::$instance)
            return self::$instance;

        $newInstance = self::getDefaultInstance();
        self::setInstance($newInstance);

        return self::$instance;
    }

    /**
     * Replace the singleton instance with a new one
     * If your class uses `Configurable` trait, its configuration is automatically loaded
     *
     * @param static $newInstance New instance to replace the current one
     */
    final public static function setInstance(self $newInstance)
    {
        if (Utils::uses($newInstance, "\Sharp\Classes\Core\Configurable"))
        {
            if (!$newInstance->isConfigurationLoaded())
                $newInstance->loadConfiguration();
        }

        self::$instance = $newInstance;
    }

    /**
     * Destroy the current instance (And therefore call `__destruct()`)
     */
    final public static function removeInstance()
    {
        self::$instance = null;
    }
}