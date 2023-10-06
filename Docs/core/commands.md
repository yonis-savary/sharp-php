[< Back to summary](../home.md)

# 💻 CLI Commands

Sharp got the [`do`](../../Core/Server/do) script, which can be used to execute commands from your terminal

## Create a command

Creating a command is very simple, all you have to do is to create a file
in your application (Preferably in a `Commands` directory)

`SuperApp/Commands/ClearCaches.php`:
```php
namespace SuperApp\Commands;

use Sharp\Classes\CLI\Args;
use Sharp\Classes\CLI\Command;

class ClearCaches extends Commands
{
    public function __invoke(Args $args)
    {
        echo "I'am clearing caches !";
    }
}
```

To execute it, type `php do <your-command>` in your terminal
```bash
php do clear-caches
# or
php do super-app@clear-caches
```
And voilà !

> [!NOTE]
> - you may have noticed, the classname was transformed into a snake-case equivalent, this is automatically made by the class
> - you can also implements the `getHelp()` method which should display a help menu/documentation when calling `php do help`

## Using Args object

The args object represent the arguments given to your command through the cli (like `--verbose`, `--help`...etc)

[`Args`](../../Classes/CLI/Args.php) most useful methods are :
```php
# Return the parameter value or null if absent
public function get(string $short, string $long);
$args->get("n", "number");

public function isPresent(string $short, string $long);
$args->isPresent("v", "verbose")

# Return the value of the parameter, `null` if the parameter is present
# but has no value, `false` is the parameter is present
public function getOption(string $short, string $long);
$args->getOption("r", "replace")
```

[< Back to summary](../home.md)