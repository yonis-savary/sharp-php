[< Back to summary](../README.md)

# 💻 CLI Commands

Sharp is shipped with the [`do`](../../Core/Server/do) script,
which is a launcher for any [`Command`](../../Classes/CLI/Command.php) classes

## Create a command

Creating a command is very simple, all you have to do is to create a file in your application (Preferably in a `Commands` directory) and create a class that extends [`Command`](../../Classes/CLI/Command.php)

`SuperApp/Commands/ClearCaches.php`:
```php
namespace SuperApp\Commands;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;

class ClearCaches extends Command
{
    public function __invoke(Args $args)
    {
        echo "I'am clearing caches !";
    }
}
```

To execute it, type this in your terminal
```bash
php do clear-caches

# or

php do super-app@clear-caches
```
And voilà !

> [!NOTE]
> - you may have noticed, the class name was transformed into a snake-case equivalent, this is automatically made by the class
> - you can also implements the `getHelp()` method which return a string that is displayed when calling `php do help`

## Args object

The args object represent the arguments given to your command through the cli (like `--verbose`, `--help`...etc)

[`Args`](../../Classes/CLI/Args.php) most useful methods are :
```php
# Return the parameter value or null if absent
# public function get(string $short, string $long);
$args->get("n", "number");

# public function isPresent(string $short, string $long);
$args->isPresent("v", "verbose")

# Return the value of the parameter, `null` if the parameter is present but has no value, `false` is the parameter is not present
# public function getOption(string $short, string $long);
$args->getOption("r", "replace")
```

[< Back to summary](../README.md)