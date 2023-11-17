[< Back to summary](../README.md)

# 📦 Setup & Configuration

## Application Directory

Sharp's autoloader is made to load multiples applications at the same time (which mean that you can split a big application into modules/services)

One application is made of those directories (All of them are optional):
- Assets
- Classes
- Controllers
- Commands
- Components
- Features
- Others
- Routes
- Helpers
- Views

> [!NOTE]
> Files in `Helpers` and `Others` are recursively loaded with `require_once`

## Configuration

Your application(s) configuration is stored inside `sharp.json`

The configuration is written as

```json
{
    "snake-case-component-name": {
        "option-name": "some-value"
    }
}
```

> [!NOTE]
> Every component's configuration is described in its respective page

If your configuration is missing some keys, or if you want to create one from nothing, you can execute

```bash
php do fill-configuration
```





## Loading an application

Let's say your application is in a directory named `ShippingApp`, to load it,
you only have to add this in your configuration

```json
"applications": [
    "ShippingApp"
]
```


> [!IMPORTANT]
> If your application contains a `vendor/autoload.php` file,
> it will be automatically required by the autoloader

Now, let's say that you want to make a module for your application named `ShippingCRM` (located in `ShippingApp/ShippingCRM`) then, you will need to add it in your configuration too

```json
"applications": [
    "ShippingApp",
    "ShippingApp/ShippingCRM"
]
```

This feature allows you to extends your application and disable any part of it just by editing your configuration

Also, you can also use the `enable-application` command to add applications in your configuration

```bash
php do enable-application ShippingApp/ShippingCRM
# This means you can also use *
php do enable-application ShippingApp/Modules/*
```

> [!IMPORTANT]
> Applications are loaded in the order they're written in your configuration
> (Beware of dependencies !)



## Namespaces

With Sharp, every namespace is set by its relative path to the project root

Example, for

```php
App/Controllers/Provider/Order.php
```

Its full namespace shall be

```php
# namespace
App\Controllers\Provider
# classname
App\Controllers\Provider\Order
```

> [!IMPORTANT]
> It is very important to respect this rule as the Autoloader cannot load a file with a bad formatted namespace


## Making custom scripts that uses Sharp

If you want to use Sharp in a PHP script, you can just
require [`Sharp/bootstrap.php`](../bootstrap.php) in your script

## Additional properties

- `Autoloader::getListFiles(Autoloader::AUTOLOAD)` can retrieve files in
    - Commands
    - Controllers
    - Classes
    - Components
    - Features
    - Models
- `Autoloader::getListFiles(Autoloader::ASSETS)` can retrieve files in
    - Assets
- `Autoloader::getListFiles(Autoloader::VIEWS)` can retrieve files in
    - Views
- `Autoloader::getListFiles(Autoloader::ROUTES)` can retrieve files in
    - Routes
- `Autoloader::getListFiles(Autoloader::REQUIRE)` can retrieve files in
    - Helpers
    - Others


[< Back to summary](../README.md)