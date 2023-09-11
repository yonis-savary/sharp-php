[< Back to summary](../home.md)

# 📁 App Directory & Config

## Configuration

Your application(s) configuration is stored inside `sharp.json`,
which contains every components/framework configuration

## Application Directory

Sharp's Autoloader has a support for multiple applications at the same time

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

**Note: every directories are optionnal**
**Files in `Helpers` and `Others` are directly included with `require_once`**

### Loading an application

Let's say your application is in a directory named `ShippingApp`, to load it,
you only have to add this in your configuration

```json
"applications": [
    "ShippingApp"
]
```

Now, let's say that you want to make a module for your application named `ShippingCRM`, which is
located in `ShippingApp/Modules/ShippingCRM`, then, you have to add it too in your configuration
(otherwise it won't be loaded)

```json
"applications": [
    "ShippingApp",
    "ShippingApp/Modules/ShippingCRM"
]
```

This allow you to extends your application and disable any part/module of it just by adding/removing it from your config

## Namespaces

Every namespace is set by its relative path, example: for `./App/Controllers/Provider/Order.php`, the classname shall be `App\Controllers\Provider` otherwise, the autoloader won't recognize it

## Additionnal properties

- `Autoloader::getListFiles(Autoloader::AUTOLOAD)` can retrieve files in `Commands`, `Controllers`, `Classes`, `Components`, `Models`
- `Autoloader::getListFiles(Autoloader::ASSETS)` can retrieve files in `Assets`
- `Autoloader::getListFiles(Autoloader::VIEWS)` can retrieve files in `Views`
- `Autoloader::getListFiles(Autoloader::ROUTES)` can retrieve files in `Routes`
- `Autoloader::getListFiles(Autoloader::REQUIRE)` can retrieve files in `Helpers` and `Others`

[< Back to summary](../home.md)