<?php

use Sharp\Classes\Http\Request;
use Sharp\Classes\Web\Router;

require_once "../Sharp/bootstrap.php";

$request = Request::buildFromGlobals();

$router = Router::getInstance();
$router->loadRoutesOrCache($request);

$response = $router->route($request);
$response->display();

