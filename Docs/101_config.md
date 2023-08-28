[< Back to summary](./000_sharp.md)

# Sharp-PHP - App Directory & Config

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

### Properties :

- `Autoloader::getListFiles(AutoLoader::AUTOLOAD)` can retrieve files in `Commands`, `Controllers`, `Classes`, `Components`, `Models`
- `Autoloader::getListFiles(AutoLoader::ASSETS)` can retrieve files in `Assets`
- `Autoloader::getListFiles(AutoLoader::VIEWS)` can retrieve files in `Views`
- `Autoloader::getListFiles(AutoLoader::ROUTES)` can retrieve files in `Routes`
- `Autoloader::getListFiles(AutoLoader::REQUIRE)` can retrieve files in `Helpers` and `Others`

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