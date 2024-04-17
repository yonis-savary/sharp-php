[< Back to summary](../README.md)

# 🧩 Sharp components

Some classes need to be accessed globally from your application (like `Configuration`, `Logger`...etc)
to resolve this, the [`Component`](../../Classes/Core/Component.php) trait was created.

To be clear: **This trait's purpose is to be a [Singleton](https://en.wikipedia.org/wiki/Singleton_pattern) that does not limit itself to one instance**

One main instance exists, and can be retrieved with `getInstance()`,
but you can still create other instances of your classes, which can be very useful

(Example: you may have one main connection to your database, but create another for some specific part of your application)

Every Component got those methods :
```php
/* Can be edited, gives a default instance of your class when getInstance() is called the for first time */
public static function getDefaultInstance();

/* Return the global instance of the class, create one with getDefaultInstance() if needed */
final public static function getInstance();

/* Replace the global instance with another one */
final public static function setInstance(self $newInstance);

/* Remove the global instance if needed (useful to call the destructor) */
final public static function removeInstance();
```

> [!NOTE]
> Apart of some edge case, the only method you should use from this trait is `getInstance()`

## ✅ Tutorial: Creating a Component

### Context

1. Our application is named `MagicShip`

### Creation

We want to create a component named `MagicOrderPrinter`, which is a class that
can print a PDF of an order and log information about it

First, we need to define our class

```php
class MagicOrderPrinter
{
    protected Logger $logger;

    public function __construct(Logger $logger=null)
    {
        $this->logger = $logger ?? new Logger(null);
    }

    public function printOrder(int $orderId)
    {
        $this->logger->info("Printing order $orderId");
        //...
    }
}
```

Then to make it a component, we need to :
1. use the `Component` trait
2. implement `getDefaultInstance()`

```php
class MagicOrderPrinter
{
    use Component;

    public static function getDefaultInstance()
    {
        return new self(new Logger("magic-printer.csv"));
    }

    /* ... */
}
```

From here, the first time we call `MagicOrderPrinter::getInstance()`,
a global instance will be created, and shall log information to `magic-printer.csv` by default

```php
# Using the default instance
MagicOrderPrinter::getInstance()->printOrder(204987);

# Create another instance for specific situations
$debugPrinter = new MagicOrderPrinter(
    new Logger("magic-printer-debug.csv")
);
$debugPrinter->printOrder(209409);
```

[< Back to summary](../README.md)