<?php

namespace Sharp\Classes\Data\ModelGenerator;

use InvalidArgumentException;
use Sharp\Classes\Core\Component;
use Sharp\Classes\Data\Database;
use Sharp\Classes\Env\Config;
use Sharp\Core\Utils;

class ModelGenerator
{
    use Component;

    protected GeneratorDriver $driver;

    public static function getDefaultInstance()
    {
        $dbConfig = Config::getInstance()->get("database", []);

        $driver = match($dbConfig["driver"] ?? null) {
            "mysql" => MySQL::class,
            "sqlite" => SQLite::class,
            default => MySQL::class
        };

        return new self($driver);
    }

    public function __construct(string $driverClass, Database $connection=null)
    {
        $connection ??= Database::getInstance();

        if (!Utils::extends($driverClass, GeneratorDriver::class))
            throw new InvalidArgumentException("[$driverClass] does not extends ". GeneratorDriver::class);

        $this->driver = new ($driverClass)($connection);
    }

    public function generateAll(string $application)
    {
        if (!is_dir($application))
            throw new InvalidArgumentException("[$application] does not exists !");
        $this->driver->generateAll($application);
    }
}