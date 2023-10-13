[< Back to summary](../home.md)

# 📃 Logging & Shortcuts

The [`Logger`](../../Classes/Core/Logger.php) class is a [`Component`](./components.md) that can be used to log information inside a CSV file

By default, you can log by calling the `log()` method

```php
$logger->log("DEBUG", "Hello there");
```

But writing log level can be quite tedious, that is why the `Logger` class got some shortcuts
```php
$logger->debug("I'm a debug line");
$logger->info("I'm an info line");
$logger->notice("I'm a notice line");
$logger->warning("I'm a warning line");
$logger->error("I'm an error line");
$logger->critical("I'm a critical line");
$logger->alert("I'm an alert line");
$logger->emergency("I'm an emergency line");

# It can also be used to log error/traces in a more verbose way
$logger->logThrowable(new Exception("Something went wrong"));

# Note : your can log everything that can somehow be represented as a string
$logger->info([1,2,3]);
$logger->info(["A"=>1, "B"=>2, "C"=>3]);
```

> [!NOTE]
> By default, `Logger` writes information into `Storage/sharp.csv`

## Advanced Usage

You can create new `Logger` objects to log information inside other files

```php
# Everything this logger get will be logged to Storage/errors.csv
$logger = new Logger("errors.csv");

# A custom Storage can also be given
# this one will log everything in /var/log/shippingService/service.csv
$logger = new Logger("service.csv", new Storage("/var/log/shippingService"))
```

You can also work with streams directly !

```php
$stdLogger = Logger::fromStream(fopen("php://output", "w"));
$stdLogger->info("Hello!"); // Display Hello! in the console/page

// Now logs into a file
$stdLogger->replaceStream(fopen("myFile.txt", "a"), true);
```

[< Back to summary](../home.md)