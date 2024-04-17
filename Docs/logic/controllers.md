[< Back to summary](../README.md)

# ⚙️ Controllers

The main purpose of controllers is to hold your code logic (and therefore, your routes callbacks)

## Plain Controllers

To create a controller, You can simply create a class with its methods and then declare its routes in a separate file

`YourApp/Controllers/MyController.php`:
```php
class MyController
{
    public static function greets()
    {
        return Response::json("Hello!");
    }
}
```

`YourApp/Routes/web.php` :
```php
Router::getInstance()->addRoutes(
    Route::get("/", [MyControllers::class, "greets"])
);
```

But as your application grows, the routes number can become confusing, knowing which route lead to which method and which method is used by which route can be hard

## Controller trait

If you want to group your logic and routes declarations, you can make a class that uses the `Controller` trait

When `Router` loads your application routes, it will call the `declareRoutes()` method of every `Controller` and give itself as a parameter

```php
class MyController
{
    use Controller;

    public static function declareRoutes(Router $router)
    {
        $router->addRoutes(
            Route::get("/", [self::class, "greets"])
            Route::view("/about", self::relativePath("about.php"))
        );
    }

    public static function greets()
    {
        return Response::json("Hello!");
    }
}
```

This structure allows you to organize your code by feature or business domain

[< Back to summary](../README.md)