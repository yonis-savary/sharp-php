[< Back to summary](../home.md)

# 📁 App Directory & Config

Before explaining how the framework is made and how to use it,
here is how to organize your application files

## Configuration

Your application(s) configuration is stored inside `sharp.json`,
which contains every components/framework configuration

## Application Directory

Sharp's Autoloader has a support for multiple applications at the same time,

One application is made of these directories:
- Assets
- Classes
- Controllers
- Commands
- Components
- Others
- Routes
- Helpers
- Views

**Note: every one of those directories are optionnal**
**Files in `Helpers` and `Others` are required directly with `require_once`**

### Loading an application

Let's say your application is in a directory named `ShippingApp`, to load it,
you only have to add this in your configuration

```json
"applications": [
    "ShippingApp"
]
```

Now let's say that you want to make a module for your application named `ShippingCRM`, and it is
located in `ShippingApp/Modules/ShippingCRM`, then, you have to edit your configuration to load it too

```json
"applications": [
    "ShippingApp",
    "ShippingApp/Modules/ShippingCRM"
]
```

This allow you to extends your application and disable any part/module of it just by editing your config

## Namespaces

Every namespace is set by its relative path, example: for `./App/Controllers/Provider/Order.php`, the classname shall be `App\Controllers\Provider` otherwise, the autoloader won't recognize it

## Additionnal properties

- `Autoloader::getListFiles(Autoloader::AUTOLOAD)` can retrieve files in `Commands`, `Controllers`, `Classes`, `Components`, `Models`
- `Autoloader::getListFiles(Autoloader::ASSETS)` can retrieve files in `Assets`
- `Autoloader::getListFiles(Autoloader::VIEWS)` can retrieve files in `Views`
- `Autoloader::getListFiles(Autoloader::ROUTES)` can retrieve files in `Routes`
- `Autoloader::getListFiles(Autoloader::REQUIRE)` can retrieve files in `Helpers` and `Others`
