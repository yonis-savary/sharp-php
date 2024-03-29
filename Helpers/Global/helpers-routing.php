<?php

use Sharp\Classes\Web\Route;
use Sharp\Classes\Web\Router;

/**
 * Shortcut to `Router::getInstance()->addRoutes()`
 */
function addRoutes(Route ...$routes): void
{
    Router::getInstance()->addRoutes(...$routes);
}


/**
 * Shortcut to `Router::getInstance()->addGroup()`
 */
function addGroup(array $group, Route ...$routes): void
{
    $router = Router::getInstance();
    $router->addGroup($group, ...$routes);
}

/**
 * Shortcut to `Router::getInstance()->groupCallback()`
 */
function groupCallback(array $group, callable $routeDeclaration): void
{
    $router = Router::getInstance();
    $router->groupCallback($group, $routeDeclaration);
}

/**
 * Shortcut to `Router::getInstance()->createGroup()`
 */
function createGroup(string|array $urlPrefix, string|array $middlewares): array
{
    return Router::getInstance()->createGroup($urlPrefix, $middlewares);
}
