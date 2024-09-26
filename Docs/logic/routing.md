[< Back to summary](../README.md)

# 🛣️ Routing

Routing in Sharp is made through two classes :
- [`Route`](../../Classes/Web/Route.php), which hold information about one route
- [`Router`](../../Classes/Web/Router.php), which hold a set of `Route` objects and is able to route a [`Request`](../../Classes/Http/Request.php) object

## Routes creation

A `Route` is made of :
- A path (URL)
- A callback, which is executed when the route is called
- Some allowed [HTTP Methods](https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods) (optionnal) that can be used to restraint its access
- Some [Middlewares](./middlewares.md) (optional) that can be used to control its access
- Some "extras": additional information that can be used by controllers/components

Routes can be created through the `Route` constructor, or
through its statical shortucts used to define which HTTP method the route allow

```php
# Directly using the constructor (not quite efficient)
new Route("/", function(){ /* Functions can be used too ! */ });

# Using shortcuts (which make them more readable)
Route::any("/", [MyClass::class, "greets"]);
Route::get("/", [MyClass::class, "greets"]);
Route::post("/", [MyClass::class, "greets"]);
Route::patch("/", [MyClass::class, "greets"]);
Route::put("/", [MyClass::class, "greets"]);
Route::delete("/", [MyClass::class, "greets"]);

```

Then, to add them to your router, call the `addRoutes()` method of any router:
```php
$router = Router::getInstance();
$router->addRoutes(
    Router::get("/login", [LoginController::class, "renderPage"]),
    Router::post("/login", [LoginController::class, "handleLogin"])
);
```

> [!NOTE]
> Routes SHOULD always have an array callback, as direct functions cannot be put in cache, and therefore, cannot be optimized by the `Router` class


## Routes grouping

You can group routes together with `Router` !

A group can apply a path prefix, middlewares and extras to a set of routes

```php
$router = Router::getInstance();
$group = ["path" => "api", "middlewares" => IsAuthenticated::class];

# ########## METHOD 1 ##########

# This is the simplest way to group routes
$router->addGroup(
    $group,
    Route::get("/user/actives", [UserController::class, "actives"]),
    Route::get("/user/blocked", [UserController::class, "blocked"])
);

# ########## METHOD 2 ##########

# Group callback is useful too !
# Every routes declared in the callback is included in the parent group
# Note: you can accumulate groups !
$router->groupCallback($group, function(Router $router)
{
    # Routes here
    # - starts with "/api"
    # - have the IsAuthenticated middleware
    $router->addRoutes(
        Route::get("/user/actives", [UserController::class, "actives"]),
        Route::get("/user/blocked", [UserController::class, "blocked"])
    );

    $router->groupCallback(["path" => "shipping"], function(Router $router)
    {
        # Routes here
        # - starts with "/api/shipping"
        # - have the IsAuthenticated middleware
        $router->addRoutes(
            Route::get("/to-ship", [ShippingController::class, "toShip"])
        );
    });
});

# ########## METHOD 3 ##########

# The last method it to add routes that are manually grouped with group()
$router->addRoutes(
    ...$router->group(
        $group,
        Route::get("/user/actives", [UserController::class, "actives"]),
        Route::get("/user/blocked", [UserController::class, "blocked"])
    )
);
```

> [!IMPORTANT]
> It is highly advised to **use one method and not mix them** , that could lead to something quite hard to read


```php
# A really bad (but working) usage !
$router->groupCallback($groupA, function(Router $router){
    $router->addGroup(
        $groupB,
        ...$router->group(
            $groupC,
            Route::get(/* ... */),
            Route::get(/* ... */),
        )
    );
})
```

## Slugs

The `Route` class has a support for path slugs (generic routes)

Here is a basic usage of it :
```php
Route::get("/contact/{id}", function(Request $req, int $id){
    # One way to get slug values it through parameters
    # They are passed in order after the Request
    echo "ContactId: " . $id;

    # The other way is through the Request object
    # in this case you need to specify the name of the slug
    echo "ContactId: " . $req->getSlug("id");
});
```

The inconvenient of this method is that anything can replace `id`, there is no
format to respect

To address this, `Route` also support predefined formats and custom regular expressions
```php
# Using predefined format
Route::get("/contact/{int:id}", function($req, $id){
    return "id is ".$id;
});
# Using custom regex
Route::get("/binary/{[01]+:number}", function($req, $number) {
    return "bin number is".$number;
});
```

Here are the formats that are currently supported

| Slug Keyword | Regex                                                                         |
|--------------|-------------------------------------------------------------------------------|
| `int`        | `\d+`                                                                         |
| `float`      | `\d+(?:\.\d+)?`                                                               |
| `date`       | `\d{4}\-\d{2}\-\d{2}`                                                         |
| `time`       | `\d{2}\:\d{2}\:\d{2}`                                                         |
| `datetime`   | `\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}`                                     |
| `hex`        | `[0-9a-fA-F]+`                                                                |
| `uuid`       | `[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}` |
| `any`        | `.+`                                                                          |


> [!IMPORTANT]
> The `any` keyword means that **ANY** part of the url is accepted (which includes slashes)
>
> (It can be used to make a fallback route)

## Additional features

- Any `Router` got `getRoutes()` method to retrieve added routes them
- The `Route::view()` function can be used to create a route that render a view when called
- The `Route::file()` function can be used to create a route that simply serve a file
- The `Route::redirect()` function can be used to create a redirection to another URL

## Caching

The Router can cache some data in order to be faster !

```json
"router": {
    "cached": true,
    "quick-routing": false
}
```

- When `cached` is enabled, the router will write some path/route pairs into cache, if you plan to use this feature to optimize your app,
please avoid using routes with slugs
- When `quick-routing` is enabled, the router will try to find a cached route as soon as the request is handled, if it is found, a
response is generated by the route and displayed directly (use this feature to optimize non-complex applications)

[< Back to summary](../README.md)