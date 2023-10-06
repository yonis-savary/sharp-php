[< Back to summary](../home.md)

# 📃 Logging & Shortcuts

The [Logger](../../Classes/Core/Logger.php) class can be used to log informations inside a CSV file

As this class is a component, it can be retrieved with `Logger::getInstance()` and used to log infos to `Storage/sharp.csv`

You can log by calling the `log()` method

```php
$logger->log("DEBUG", "Hello there");
```

But writting log level can be quite tedious, that is why the `Logger` class got some shortcuts
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

Yet, calling `Logger::getInstance()` or store it inside a variable can be tedious too ! (Especially if you're debugging your application), to address this, some shortcuts where made in [Helpers/helpers-log.php](../../Helpers/helpers-log.php)

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

## Advanced Usage

You may want to create a new Logger to log informations inside another file

```php
# Everything this logger get will be logged to Storage/errors.csv
$logger = new Logger("errors.csv");

# A custom Storage can also be given
# will log everything in /var/log/shippingService/service.csv
$logger = new Logger("service.csv", new Storage("/var/log/shippingService"))
```

Or work with stream directly
```php
$stdLogger = Logger::fromStream(fopen("php://output", "w"));
$stdLogger->info("Hello!"); // Display Hello! in the console/page

// Replace the stream to redirect the output
$stdLogger->replaceStream(fopen("myFile.txt", "a"), true);
```

[< Back to summary](../home.md)