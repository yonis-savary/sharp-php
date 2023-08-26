<?php

namespace Sharp\Classes\Core;

use Sharp\Classes\Env\Config;

/**
 * Configurable classes holds a variable named `$configuration`
 * The configuration is loaded from the config and `getConfigurationInstance()`
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
     * The default implementation transform your classname into its snake_case equivalent
     *
     * @example name `MyComponentThatFetch` will give `my_component_that_fetch`
     * @return string Key to load in the configuration
     */
    public static function getConfigurationKey(): string
    {
        $class = self::class;
        $class = preg_replace("/.+\\\\/", "", $class);
        $class = preg_replace("/([a-z])([A-Z])/", '$1-$2', $class);
        $class = strtolower($class);
        return $class;
    }

    public static function getConfigurationInstance(): Config
    {
        return Config::getInstance();
    }

    /**
     * Load the configuration from the configuration instance given by `getConfigurationInstance()`
     * and the key given by `getConfigurationKey()`
     */
    public function loadConfiguration()
    {
        $config = self::getConfigurationInstance();
        $this->configuration = array_merge(
            self::getDefaultConfiguration(),
            $config->get(self::getConfigurationKey(), [])
        );
        $this->configurationIsLoaded = true;
    }

    /**
     * @return bool `true` or `false` depending if the configuration is loaded
     */
    public function configurationIsLoaded(): bool
    {
        return $this->configurationIsLoaded;
    }

    /**
     * Default method that return `true` or `false` depending on the `enabled` key
     *
     * @return bool Is the class/component enabled ?
     */
    public function isEnabled(): bool
    {
        return boolval($this->configuration["enabled"] ?? false);
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function setConfiguration(array $config)
    {
        $this->configuration = $config;
    }
}