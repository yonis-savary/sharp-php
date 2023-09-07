<?php

namespace Sharp\Classes\Core;

use Sharp\Classes\Env\Config;

/**
 * Configurable classes can be configured through any Config object
 * To implement a Configurable class:
 * 1. implements `getDefaultConfiguration()` which return a complete default configuration
 * 2. Use `getConfiguration()` to load/read the configuration
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
     * @param Config $config Config to read from (global instance is used if `null`)
     * @return array Configuration merged with the default one
     */
    final public static function readConfiguration(Config $config=null): array
    {
        $config ??= Config::getInstance();
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
     * Default method that return `true` or `false` depending on the `enabled` key
     *
     * @return bool Is the class/component enabled ?
     */
    final public function isEnabled(): bool
    {
        return boolval($this->getConfiguration()["enabled"] ?? false);
    }

    final public function getConfiguration(Config $config=null): array
    {
        if ((!$this->configurationIsLoaded()) || $config)
            $this->setConfiguration(self::readConfiguration($config));

        return $this->configuration;
    }

    /**
     * Overwrite the configuration
     * (Also merge it with the current one if some keys are missing)
     */
    final public function setConfiguration(array $newConfiguration): void
    {
        $this->configuration = array_merge(
            $this->configuration ?? self::getDefaultConfiguration(),
            $newConfiguration
        );
        $this->configurationIsLoaded = true;
    }
}