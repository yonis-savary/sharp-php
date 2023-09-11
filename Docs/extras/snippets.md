[< Back to summary](../home.md)

# 💬 Q/A & Snippets

This document hold some code snippets to learn the framework faster

You can search (Ctrl+F) some tags like (Autoload, Config...etc)


## Setup - Creating an App

tags: directory, application

1. Create `YourAppName` directory (or `[AnySubDir/...]/YouAppName`)
2. Add relative path to `YourAppName` directory to `applications` in `sharp.json`

## Logic - Adding routes

tags: routing, routes

`YourAppName/Routes/anyfile.php`
```php
# helpers method: addRoutes()
Router::getInstance()->addRoutes(
    Route::get("/path", [Controller::class, "method"])
);
```

## Data - Fetching data from database

tags: data, database, query

```php
# helpers method: query()
Database::getInstance()->query(
    "SELECT * FROM user WHERE login = {}",
    ['admin']
);
```

## Web - Render a view

tag: view, template, html, render

```php
# helpers method: render()
Renderer::getInstance()->render(
    "directory/viewname",
    ["name" => "Paul"]
)
```
`viewname.php`:
```php
<p>Hello <?= $name ?> </p>
```

[< Back to summary](../home.md)