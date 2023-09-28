<?php

/**
 * This file includes some fixes for specific systems
 *
 * Fixes are put here to make components code cleaner
 */


$handler = Sharp\Classes\Core\Events::getInstance();


/**
 * ForeignKeys on SQLite PDO Objects must be manually enabled
 */
$handler->on("connectedDatabase", function($event){
    if ($event["driver"] === "sqlite")
        $event["connection"]->query("PRAGMA foreign_keys=ON");
});