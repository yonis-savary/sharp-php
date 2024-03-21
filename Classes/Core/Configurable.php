<?php

namespace Sharp\Classes\Core;

use Sharp\Classes\Env\Configuration;

/**
 * Configurable classes can be configured through any Configuration object
 * To implement a Configurable class:
 * 1. implements `getDefaultConfiguration()` which return a complete default configuration
 * 2. Use `getConfiguration()` to load/read the configuration
 */
trait Configurable
{
    protected array $configuration = [];
    protected bool $configurationIsLoaded = false;

    /**
     * @return array Default configuration to use (merged with the actual as a default)
     */
    public static function getDefaultConfiguration(): array
    {
        return [];
    }

    public function __construct()
    {
        $this->loadConfiguration();
    }

    /**
     * This function describe which key must be loaded from the configuration
     * The default implementation transform your classname into its kebab-case equivalent
     *
     * @example name `MyComponentThatFetch` will give `my-component-that-fetch`
     * @return string Key to load in the configuration
     * @note It is not advised (but not forbidden) to override this method
     */
    public static function getConfigurationKey(): string
    {
        $class = self::class;
        $class = preg_replace("/.+\\\\/", "", $class);
        $class = preg_replace("/([a-z])([A-Z])/", '$1-$2', $class);
        $class = strtolower($class);
        return $class;
    }

    /**
     * Return an array from given configuration
     *
     * @param Configuration $config Configuration to read from (global instance is used if `null`)
     * @return array Configuration merged with the default one
     */
    final public static function readConfiguration(Configuration $config=null): array
    {
        $config ??= Configuration::getInstance();

        return array_merge(
            self::getDefaultConfiguration(),
            $config->get(self::getConfigurationKey(), [])
        );
    }

    /**
     * @return bool `true` or `false` depending if the configuration is loaded
     */
    final public function configurationIsLoaded(): bool
    {
        return $this->configurationIsLoaded;
    }

    /**
     * Overwrite the configuration
     * (Also merge it with the current one if some keys are missing)
     */
    final public function setConfiguration(array $newConfiguration): void
    {
        $this->configuration = array_merge(
            self::getDefaultConfiguration(),
            $this->configuration,
            $newConfiguration
        );
        $this->configurationIsLoaded = true;
    }

    final public function loadConfiguration(Configuration $config=null): void
    {
        if ((!$this->configurationIsLoaded()) || $config)
            $this->setConfiguration(self::readConfiguration($config));
    }

    final public function getConfiguration(): array
    {
        return $this->configuration;
    }

    /**
     * @param string $key Key to check
     * @return bool Is the key `true` ?
     */
    final public function is(string $key): bool
    {
        return boolval($this->getConfiguration()[$key] ?? false);
    }

    /**
     * Default method that return `true` or `false` depending on the `enabled` key
     *
     * @return bool Is the class/component enabled ?
     */
    final public function isEnabled(): bool
    {
        return $this->is("enabled");
    }

    /**
     * Default method that return `true` or `false` depending on the `cached` key
     *
     * @return bool Is the class/component enabled ?
     */
    final public function isCached(): bool
    {
        return $this->is("cached");
    }
}