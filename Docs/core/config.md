[< Back to summary](../home.md)

# 📦 App Directory & Configuration

## Application Directory

Sharp's core can load multiples applications at the same time (which mean that you can split a big application into modules/services)

One application is made of those directories (Everyone of them are optionnal):
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
> (Sub)files in `Helpers` and `Others` are directly included with `require_once`

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

### Loading an application

Let's say your application is in a directory named `ShippingApp`, to load it,
you only have to add this in your configuration

```json
"applications": [
    "ShippingApp"
]
```

Now, let's say that you want to make a module for your application named `ShippingCRM`, which is
located in `ShippingApp/ShippingCRM`, then, you have to add it too in your configuration
(otherwise it won't be loaded)

```json
"applications": [
    "ShippingApp",
    "ShippingApp/ShippingCRM"
]
```

This allow you to extends your application and disable any part/module of it just by adding/removing it from your configuration

## Namespaces

Every namespace is set by its relative path to the project root

Example, for

```App/Controllers/Provider/Order.php```

the namespace shall be

```App\Controllers\Provider```

and the classname `Order`

> [!IMPORTANT]
> It is very important to respect this rule as the Autoloader cannot load a file with a bad formatted namespace


## Making custom scripts that uses Sharp

If you want to use Sharp in a PHP script, you can just
require [`Sharp/bootstrap.php`](../bootstrap.php)

## Additionnal properties

- `Autoloader::getListFiles(Autoloader::AUTOLOAD)` can retrieve files in
    - `Commands`,
    - `Controllers`,
    - `Classes`,
    - `Components`,
    - `Features`,
    - `Models`
- `Autoloader::getListFiles(Autoloader::ASSETS)` can retrieve files in
    - `Assets`
- `Autoloader::getListFiles(Autoloader::VIEWS)` can retrieve files in
    - `Views`
- `Autoloader::getListFiles(Autoloader::ROUTES)` can retrieve files in
    - `Routes`
- `Autoloader::getListFiles(Autoloader::REQUIRE)` can retrieve files in
    - `Helpers`
    - `Others`


[< Back to summary](../home.md)