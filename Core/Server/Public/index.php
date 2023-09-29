<?php

use Sharp\Classes\Http\Request;
use Sharp\Classes\Web\Router;

require_once "../Sharp/bootstrap.php";

$request = Request::buildFromGlobals();
$request->logSelf();

$router = Router::getInstance();

$response = $router->route($request);
$response->logSelf();
$response->display();

