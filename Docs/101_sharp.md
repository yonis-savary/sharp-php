# Sharp Framework

The goals behind Sharp are :
- Removing unecessary abstraction layers
- Make a clean code that is intuitive for the most
- Automate tedious task (like model creation)
- Let you build your app as fast as possible and don't have to worry about setup
- Have a framework that doesn't break your IDE, PHP type hint should be enough in the vast majority of situations
- Have as few dependencies as possible (So far, `composer.json` only install [PHPUnit](https://phpunit.de/))

This documentation directory holds informations about how the framework works, but
every component/classes documentation are held in their respective files, here is a summary:

Hand-written documentation:
- 🧩 [Understanding Sharp components](./102_components.md)
- 📍 [Routing](./103_routing.md)
- ⚙️  [Controllers](./104_controllers.md)
- 🚦 [Middlewares](./105_middlewares.md)
- 💻 [Creating a CLI command](./105_commands.md)
- 📁 [Serve assets with AssetServer](./201_assets.md) (TODO)
- 📖 [Work with database models](./301_models.md) (TODO)
- 🧲 [Automatic API for your models with Autobahn](./302_autobahn.md) (TODO)

File/Comment documentation:
- [`Events`](../Classes/Core/Events.php)
- [`Logger`](../Classes/Core/Logger.php)
- [`Database`](../Classes/Data/Database.php)
- [`Cache`](../Classes/Env/Cache.php)
- [`Config`](../Classes/Env/Config.php)
- [`Session`](../Classes/Env/Session.php)
- [`Storage`](../Classes/Env/Storage.php)
- [`Request`](../Classes/Http/Request.php)
- [`Response`](../Classes/Http/Response.php)

## Sharp base rules

Base rules to know are :
1. Every class/trait/interface namespace is set by its relative path, exemple: for
`./App/Controllers/Provider/Order.php`, the classname must be `App\Controllers\Provider` otherwise, the autoloader
won't recognize it


## Making custom scripts that uses Sharp

If you want to use Sharp in any of your PHP script, you can just
require [`Sharp/bootstrap.php`](../bootstrap.php), it will initialize
the framework without doing anything