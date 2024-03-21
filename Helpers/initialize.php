<?php

/*

This file initialize components that need
to be initialized when a request is handled

*/

use Sharp\Classes\Extras\AssetServer;

// Create a global instance, if enabled, handle the request
AssetServer::getInstance();