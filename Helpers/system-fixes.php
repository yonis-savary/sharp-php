<?php

/**
 * This file includes some fixes for specific systems
 *
 * Fixes are put here to make components code cleaner
 */

use Sharp\Classes\Core\EventListener;
use Sharp\Classes\Events\ConnectedDatabase;

/**
 * ForeignKeys on SQLite PDO Objects must be manually enabled
 */
EventListener::getInstance()->on(ConnectedDatabase::class, function(ConnectedDatabase $event){
    if ($event->driver === "sqlite")
        $event->connection->query("PRAGMA foreign_keys=ON");
});