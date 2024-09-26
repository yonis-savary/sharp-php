<?php

/*
    This file execute code that need to be called as the framework initialize itself
    (Used for process that need to be fast and optimizations)
*/

use Sharp\Classes\Core\EventListener;
use Sharp\Classes\Events\LoadedFramework;
use Sharp\Classes\Extras\AssetServer;
use Sharp\Classes\Web\Router;

// AssertServer don't use a Route object to handle request
// It process it as soon as the framework loads
AssetServer::getInstance();

// The quick routing route the request as soon as it arrives
// The router need to be cached in order to use the quick-routing
// See doc. for more
EventListener::getInstance()->on(LoadedFramework::class, function(){
    Router::getInstance()->executeQuickRouting();
});