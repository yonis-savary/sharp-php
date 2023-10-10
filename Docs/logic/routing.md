[< Back to summary](../home.md)

# 🛣️ Routing

Routing in Sharp is made through two classes :
- [`Route`](../../Classes/Web/Route.php): hold informations about one specific route
- [`Router`](../../Classes/Web/Router.php): hold a set of `Route` instances and is able to route a [`Request`](../../Classes/Http/Request.php) object

## Routes creation

A `Route` is made of those informations :
- A path (URL)
- A callback, which is executed when the route is called
- Some allowed [HTTP Methods](https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods) (optionnal) that can be used to restraint its access
- Some [Middlewares](./middlewares.md) (optionnal) that can be used to control its access
- Some "extras": additionnal informations that can be used by controllers/components

Routes can be created through the `Route` constructor, or
through its statical shortucts used to define which HTTP method the route allow

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

> [!NOTE]
> Routes SHOULD always have an array callback, as direct functions cannot be put in cache, and therefore, cannot be optimized by the `Router` class

## Routes grouping

You can group routes together with your router !
Routes can be grouped by path and middlewares

```php
$router = Router::getInstance();
$group = ["path" => "api", "middlewares" => IsAuthenticated::class];

# ---------- METHOD 1 ----------

# This is the simplest way to group routes
$router->addGroup(
    $group,
    Route::get("/user/actives", [UserController::class, "actives"]),
    Route::get("/user/blocked", [UserController::class, "blocked"])
);

# ---------- METHOD 2 ----------

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
        # - starts with "/api/contact"
        # - have the IsAuthenticated middleware
        $router->addRoutes(
            Route::get("/to-ship", [ShippingController::class, "toShip"])
        );
    });
});

# ---------- METHOD 3 ----------

# The last method it to manually add routes that are already grouped with group()
$router->addRoutes(
    ...$router->group(
        $group,
        Route::get("/user/actives", [UserController::class, "actives"]),
        Route::get("/user/blocked", [UserController::class, "blocked"])
    )
);
```

It is advised to use one method and not mix them, that could lead to something quite hard to read

```php
# A really bad (but working) usage !
$router->groupCallback($myGroup, function(Router $router){
    $router->addGroup(
        $group,
        Route::get(/* ... */),
        ...$router->group(
            $group,
            Route::get(/* ... */),
            Route::get(/* ... */),
        ),
        Route::get(/* ... */)
    );
})
```

## Slugs

The `Route` class has a support for path slugs (generic routes)

Here is a basic usage of it :
```php
Route::get("/contact/{id}", function(Request $req, int $id){
    echo "ContactId: " . $id;
    echo "ContactId: " . $req->getSlug("id");
});
```

The inconvenient of this method is that anything can replace `id`, there is no
format to respect.

To address this, `Route` also support predefined format and custom regexes
```php
# Using predefined format
Route::get(
    "/contact/{int:id}",
    fn($req, $id)=> "id is ".$id
);
# Using custom regex
Route::get(
    "/binary/{[01]+:number}",
    fn($req, $number) => "bin number is".$number
);
```

Here are the format that are currently supported
| Slug Keyword | Regex                                     |
|--------------|-------------------------------------------|
| `int`        | `\d+`                                     |
| `float`      | `\d+(?:\.\d+)?`                           |
| `date`       | `\d{4}\-\d{2}\-\d{2}`                     |
| `time`       | `\d{2}\:\d{2}\:\d{2}`                     |
| `datetime`   | `\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}` |
| `any`        | `.+`                                      |


> [!IMPORTANT]
> The `any` keyword means that **ANY** part of the url is accepted (which includes slashes)

## Additionnal features

- Any router got `getRoutes()` method to retrieve added routes them
- The `Route::view()` method can be used to create a route that render a view when called
- The `Route::redirect()` method can be used to create a redirection to another URL
- The [helpers-routing.php](../../Helpers/helpers-routing.php) file got two useful function to declare routes

```php
groupRoutes(["middlewares" => TokenMiddleware::class], function()
{
    addRoutes(
        Route::get("/", fn()=>"Hello"),
        Router::view("/about", "aboutPage"),
        Router::redirect("/contact", "/#contact")
    );
});
```

[< Back to summary](../home.md)