[< Back to summary](../README.md)

# 💬 Q/A & Snippets

This document hold some code snippets to learn the framework faster

You can search (Ctrl+F) some tags like (autoload, configuration, routing...etc)

## 🔵 Setup - Creating an App

tags: directory, application

1. Create `YourAppName` directory (or `[AnySubDir/...]/YouAppName`)
2. Add relative path to `YourAppName` directory to `applications` in `sharp.json`

## 🔵 Logic - Adding routes

tags: routing, routes, routing

```php
# YourAppName/Routes/anyfile.php
Router::getInstance()->addRoutes(
    Route::get("/path", [Controller::class, "method"])
);

# Helper global function
addRoutes(/*...*/);
```

## 🔵 Logic - Creating a Controller

tags: controller, routes, routing

```php
# YourAppName/Controllers/MyController.php
class MyController
{
    use Controller;

    public static function declareRoutes(Router $router)
    {
        $router->addRoutes(
            Route::get("/some-path", [self::class, "myMethod"])
        );
    }

    public static function myMethod(Request $request)
    {

    }
}
```

## 🔵 Data - Fetching data from database

tags: data, database, query

```php
Database::getInstance()->query(
    "SELECT * FROM user WHERE login = {}",
    ['admin']
);

# Global helper function
query(/*...*/);
```

## 🔵 Web - Render a view

tags: view, template, html, render

```php
Renderer::getInstance()->render(
    "directory/view_name",
    ["name" => "Paul"]
);

# Global query function
render(/*...*/);
```
`view_name.php`:
```php
<p>Hello <?= $name ?> </p>
```

[< Back to summary](../README.md)