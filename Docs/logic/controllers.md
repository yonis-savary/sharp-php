[< Back to summary](../home.md)

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

## Controller trait

If you want to group your logic and routes declarations, you can make your class use the `Controller` trait, which has the `declareRoutes()` method that is called when your application's routes are loaded

```php
class MyController
{
    use Controller;

    public static function declareRoutes(Router $router)
    {
        $router->addRoutes(
            Route::get("/", [self::class, "greets"])
        );
    }

    public static function greets()
    {
        return Response::json("Hello!");
    }
}
```

This structure allows you to organize your code by feature or domain

<!-- @todo Write docs for Controller->view()|asset() -->

[< Back to summary](../home.md)