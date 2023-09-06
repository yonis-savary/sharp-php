<?php

namespace Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use Sharp\Classes\Env\Config;
use Sharp\Tests\Classes\DummyConfigurable;

class ConfigurableTest extends TestCase
{
    public function test_getDefaultConfiguration()
    {
        $this->assertEquals([
            "enabled" => true,
            "cached" => false
        ], DummyConfigurable::getDefaultConfiguration());
    }

    public function test_getConfigurationKey()
    {
        $this->assertEquals(
            "dummy-configurable",
            DummyConfigurable::getConfigurationKey()
        );
    }

    public function test_readConfiguration()
    {
        $configData = ["enabled" => false, "cached" => false];

        $config = new Config();
        $config->set("dummy-configurable", $configData);

        $this->assertEquals(
            $configData,
            DummyConfigurable::readConfiguration($config)
        );
    }

    public function test_configurationIsLoaded()
    {
        $instance = new DummyConfigurable();

        $this->assertFalse($instance->configurationIsLoaded());

        $instance->getConfiguration();
        $this->assertTrue($instance->configurationIsLoaded());
    }

    public function test_isEnabled()
    {
        $instance = new DummyConfigurable();

        $instance->setConfiguration(["enabled" => false]);
        $this->assertFalse($instance->isEnabled());

        $instance->setConfiguration(["enabled" => true]);
        $this->assertTrue($instance->isEnabled());
    }

    public function test_getConfiguration()
    {
        $configData = ["enabled" => false, "cached" => false];

        $config = new Config();
        $config->set("dummy-configurable", $configData);

        $instance = new DummyConfigurable();
        $instance->getConfiguration($config);

        $this->assertEquals(
            $configData,
            $instance->getConfiguration()
        );
    }

    public function test_setConfiguration()
    {
        $instance = new DummyConfigurable();
        $instance->setConfiguration([]);

        $this->assertEquals(["enabled" => true, "cached" => false], $instance->getConfiguration());

        $instance->setConfiguration(["enabled" => false]);
        $this->assertEquals(["enabled" => false, "cached" => false], $instance->getConfiguration());

        $instance->setConfiguration(["cached" => true]);
        $this->assertEquals(["enabled" => false, "cached" => true], $instance->getConfiguration());
    }
}