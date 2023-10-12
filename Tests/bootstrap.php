<?php

use Sharp\Classes\Core\EventListener;
use Sharp\Classes\Core\Logger;
use Sharp\Classes\Data\Database;
use Sharp\Classes\Data\ModelGenerator\ModelGenerator;
use Sharp\Classes\Data\ObjectArray;
use Sharp\Classes\Env\Cache;
use Sharp\Classes\Env\Configuration;
use Sharp\Classes\Env\Storage;
use Sharp\Core\Autoloader;
use Sharp\Core\Utils;

require_once __DIR__ . "/../bootstrap.php";

/*

This script purpose is to be an alternative to /Sharp/bootstrap.php

The goal is to make a good envrionment to Test (with Database, Configuration...etc)
------------------------------------------------

*/

EventListener::removeInstance();

$defaultStorage = Storage::getInstance();
$defaultLogger = Logger::getInstance();

Autoloader::loadApplication("Sharp/Tests");

$testStorage = new Storage(Utils::relativePath("Sharp/Tests/tmp_test_storage"));

Storage::setInstance($testStorage);
Configuration::setInstance(new Configuration(Utils::relativePath("Sharp/Tests/config.json")));
Cache::setInstance(new Cache($testStorage, "Cache"));

$database = Database::getInstance();

$schema = file_get_contents( __DIR__."/schema.sql");

$schema = ObjectArray::fromExplode(";", $schema)
->map(trim(...))
->filter()
->collect();

foreach ($schema as $line)
    $database->query($line);

$generator = ModelGenerator::getInstance();
$generator->generateAll(Utils::relativePath("Sharp/Tests"));

/*
    Remove every files in the Test Storage before deleting the directory
*/
register_shutdown_function(function () use (&$testStorage){

    $files = array_reverse($testStorage->exploreDirectory(mode: Storage::ONLY_FILES));
    $dirs = array_reverse($testStorage->exploreDirectory(mode: Storage::ONLY_DIRS));

    foreach ($files as $file)
        $testStorage->unlink($file);

    foreach ($dirs as $directory)
        $testStorage->removeDirectory($directory);

    $testStorage->removeDirectory($testStorage->getRoot());
});