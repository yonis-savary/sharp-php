[< Back to summary](./101_sharp.md)

# Sharp-PHP - Routing

Routing in Sharp is made through two classes :
- [`Route`](../Classes/Web/Route.php): hold informations about ONE specific route
- [`Router`](../Classes/Web/Router.php): hold a set of routes and is able to route a [`Request`](../Classes/Http/Request.php) object


## Routes creation

A `Route` object got those informations :
- A path (URI)
- A callback, that is executed when called
- Some allowed HTTP Methods (optionnal) that can be used to restraint its access
- Some [Middlewares](./105_middlewares.md) (optionnal) that can be used to control its access
- Some "extras": additionnal informations about it that can be used by controllers/components

Routes can be created through the `Route` constructor, or
some statical methods used to define which HTTP method the route allow

```php
Route::get("/", [MyClass::class, "greets"]);
Route::post("/", [MyClass::class, "greets"]);
Route::patch("/", [MyClass::class, "greets"]);
Route::put("/", [MyClass::class, "greets"]);
Route::delete("/", [MyClass::class, "greets"]);
new Route("/", function(){ /* Functions can be used too ! */ });
```

Then, to add them to your router, call the `addRoutes()` method of any router:
```php
$router = Router::getInstance();
$router->addRoutes(
    Router::get("/login", [LoginController::class, "renderPage"]),
    Router::post("/login", [LoginController::class, "handleLogin"])
);
```


## Routes grouping

You can group routes together with the `group()` method of any router !
Routes can be grouped by path and middlewares

```php
$router = Router::getInstance();

$router->group(["path" => "api"], function($router){
    // Every declared routes in this function has a path that begins with "/api"
    $router->addRoutes(
        Route::get("/user/blocked", [UserController::class, "getBlockedList"])
    );

    $router->group(["middlewares" => AdminOnlyMiddleware::class], function($router){
        // Every routes here begins with "/api" and have the AdminOnlyMiddleware applied to them
        $router->addRoutes(...);
    });
});
```

## Additionnal features

- Any router got the `deleteRoutes()` method to clear its routes, and the `getRoutes()` method to retrieve them
- The `Route::view()` method can be used to create a route that render a view when called
- The `Route::redirect()` method can be used to create a redirection to another URL