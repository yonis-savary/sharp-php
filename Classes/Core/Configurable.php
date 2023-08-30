<?php

namespace Sharp\Classes\Core;

use Sharp\Classes\Env\Config;

/**
 * Configurable classes can be configured through any Config object
 * To implement a Configurable class:
 * 1. implements `getDefaultConfiguration()` which return a complete default configuration
 * 2. call `$this->loadConfiguration()` in your constructor/code
 */
trait Configurable
{
    protected array $configuration;
    protected bool $configurationIsLoaded = false;

    /**
     * @return array Default configuration to use (merged with the actual as a default)
     */
    public static function getDefaultConfiguration(): array
    {
        return [];
    }

    /**
     * This function describe which key must be loaded from the configuration
     * The default implementation transform your classname into its kebab-case equivalent
     *
     * @example name `MyComponentThatFetch` will give `my-component-that-fetch`
     * @return string Key to load in the configuration
     * @note It is not advised to override this method but not forbidden
     */
    public static function getConfigurationKey(): string
    {
        $class = self::class;
        $class = preg_replace("/.+\\\\/", "", $class);
        $class = preg_replace("/([a-z])([A-Z])/", '$1-$2', $class);
        $class = strtolower($class);
        return $class;
    }

    final public static function readConfiguration(Config $config=null): array
    {
        $config ??= Config::getInstance();
        return array_merge(
            self::getDefaultConfiguration(),
            $config->get(self::getConfigurationKey(), [])
        );
    }

    /**
     * Load the configuration from the given or global Config instance
     * and the key given by `getConfigurationKey()`
     */
    final public function loadConfiguration(Config $config=null)
    {
        $this->configuration = self::readConfiguration($config);
        $this->configurationIsLoaded = true;
    }

    /**
     * @return bool `true` or `false` depending if the configuration is loaded
     */
    final public function configurationIsLoaded(): bool
    {
        return $this->configurationIsLoaded;
    }

    /**
     * Default method that return `true` or `false` depending on the `enabled` key
     *
     * @return bool Is the class/component enabled ?
     */
    final public function isEnabled(): bool
    {
        return boolval($this->configuration["enabled"] ?? false);
    }

    final public function getConfiguration(): array
    {
        return $this->configuration;
    }

    final public function setConfiguration(array $config)
    {
        $this->configuration = $config;
    }
}