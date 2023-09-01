[< Back to summary](../home.md)

# 📃 Logging & Shortcuts

Sharp got the [Logger](../../Classes/Core/Logger.php) class that can be used to log informations inside a log file

As this class is a component, it can be retrieved with `Logger::getInstance()` and then used to log text to `Storage/sharp.csv`

You can log by calling the `log()` method

```php
$logger->log("DEBUG", "Hello there");
```

But writting log level can be quite tedious, that is why the `Logger` class got those shortcut

```php
$logger->debug("I'm a debug line");
$logger->info("I'm an info line");
$logger->notice("I'm a notice line");
$logger->warning("I'm a warning line");
$logger->error("I'm an error line");
$logger->critical("I'm a critical line");
$logger->alert("I'm an alert line");
$logger->emergency("I'm an emergency line");

# It can also be used to log error in a more verbose way
$logger->logThrowable(new Exception("Something went wrong"));

# Note that almost everything that can be represented as a string somehow can be logged
$logger->info([1,2,3]);
$logger->info(["A"=>1, "B"=>2, "C"=>3]);

// Display logs to stdout
$logger = Logger::fromStream(fopen("php://output", "w"));
$logger->info("Hello output !");
```

And yet, calling `Logger::getInstance()` or store it inside an object can be tedious too
(especially if you're debugging your application),
to address this, some shortcuts where made in [Helpers/helpers.php](../../Helpers/helpers.php)

```php
# Log every line with Logger::getInstance()
debug("I'm a debug line");
info("I'm an info line");
notice("I'm a notice line");
warning("I'm a warning line");
error("I'm an error line");
critical("I'm a critical line");
alert("I'm an alert line");
emergency("I'm an emergency line");
```

## Create a new Logger

You may want to create a new Logger to log informations inside another file

```php
# Everything this logger get will be logged to Storage/errors.csv
$logger = new Logger("errors.csv");

# A custom Storage can also be given
# will log everything in /var/log/shippingService/service.csv
$logger = new Logger("service.csv", new Storage("/var/log/shippingService"))
```


[< Back to summary](../home.md)