[< Back to summary](../home.md)

# ⚙️ Controllers

The main purpose of controllers is to hold your code logic (and therefore your routes callbacks)

## Plain Controllers

You can simply create a controller by creating a class with its methods
and then declare its routes in a separate file

`App/Controllers/MyController.php`:
```php
class MyController
{
    public static function greets()
    {
        return Response::json("Hello!");
    }
}
```

`App/Routes/web.php` :
```php
Router::getInstance()->addRoutes(
    Route::get("/", [MyControllers::class, "greets"])
);
```


## Extended Controllers

If you want to group your logic and routes declaration, you can make your class
use the `Controller` trait, which has the `declareRoutes()` method that is called
when the routes of your app are loaded

```php
class MyController
{
    use Controller;

    public static function declareRoutes()
    {
        Router::getInstance()->addRoutes(
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


[< Back to summary](../home.md)