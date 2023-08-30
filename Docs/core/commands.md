[< Back to summary](../home.md)

# 💻 CLI Commands

Sharp got the [`do`](../../../do) script, which can be used to execute
commands from your Terminal

## Create a command

Creating a command is very simple, all you have to do is to create a file
in your application (Preferably in a `Commands` directory), exemple:

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

Then, to execute it, type
```bash
php do clear-caches
# or
php do super-app@clear-caches
```
in your terminal, and voilà !

Note: you can also implements the `getHelp()` method which should display a help menu/documentation

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