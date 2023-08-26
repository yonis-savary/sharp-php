<?php

use Sharp\Classes\Core\Logger;
use Sharp\Classes\Data\Database;
use Sharp\Classes\Data\ModelGenerator\ModelGenerator;
use Sharp\Classes\Env\Cache;
use Sharp\Classes\Env\Config;
use Sharp\Classes\Env\Storage;
use Sharp\Core\Autoloader;
use Sharp\Core\Utils;

require_once __DIR__ . "/../bootstrap.php";

Autoloader::addToList(Autoloader::VIEWS, Utils::relativePath("Sharp/Tests/Views"));
Autoloader::addToList(Autoloader::AUTOLOAD, Utils::relativePath("Sharp/Tests/Middlewares"));
Autoloader::addToList(Autoloader::AUTOLOAD, Utils::relativePath("Sharp/Tests/Classes"));
Autoloader::addToList(Autoloader::ASSETS, Utils::relativePath("Sharp/Tests/Assets"));

$defaultStorage = Storage::getInstance();
$defaultLogger = Logger::getInstance();

$tmpStorage = new Storage(Utils::relativePath("Sharp/Tests/tmp_test_storage"));
Storage::setInstance($tmpStorage);

$tmpConfig = new Config();
$tmpConfig->set("database", [
    "driver" => "sqlite",
    "database" => null
]);
Config::setInstance($tmpConfig);

Cache::setInstance( new Cache($tmpStorage, "Cache")  );


$database = Database::getInstance();

$schema = file_get_contents( __DIR__."/schema.sql");
$schema = explode(";", $schema);
$schema = array_map("trim", $schema);
$schema = array_filter($schema);
foreach ($schema as $line)
    $database->query($line);

ModelGenerator::getInstance()->generateAll(Utils::relativePath("Sharp/Tests"));

register_shutdown_function(function () use (&$tmpStorage){

    $files = array_reverse($tmpStorage->exploreDirectory(mode: Storage::ONLY_FILES));
    $dirs = array_reverse($tmpStorage->exploreDirectory(mode: Storage::ONLY_DIRS));

    foreach ($files as $file)
        $tmpStorage->unlink($file);

    foreach ($dirs as $directory)
        $tmpStorage->removeDirectory($directory);

    $tmpStorage->removeDirectory($tmpStorage->getRoot());
});
// */