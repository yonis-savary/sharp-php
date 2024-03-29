<?php

use Sharp\Classes\Data\Database;

/**
 * Shortcut to `Database::getInstance()->build()`
 *
 * @param string $query SQL Query with placeholders (`{}`)
 * @param array $context Ordered array, given values will replace query's placeholders
 * @return string Built query
 */
function buildQuery(string $query, array $context=[]): string
{
    return Database::getInstance()->build($query, $context);
}

/**
 * Shortcut to `Database::getInstance()->query()`
 *
 * Execute a query and return the result
 *
 * @param string $query SQL Query with placeholders (`{}`)
 * @param array $context Ordered array, given values will replace query's placeholders
 * @return array Query result rows (raw, associative array)
 */
function query(string $query, array $context=[]): array
{
    return Database::getInstance()->query($query, $context);
}


function lastInsertId(): int|false
{
    return Database::getInstance()->lastInsertId();
}
