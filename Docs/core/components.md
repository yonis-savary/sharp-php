[< Back to summary](../home.md)

# 🧩 Sharp components

Some classes/features need to be accessed globally from your application (like `Database`, `Logger`...etc)
to resolve this, the [`Component`](../../Classes/Core/Component.php) trait was created.

**This trait purpose is to be a [Singleton](https://en.wikipedia.org/wiki/Singleton_pattern) that
does not limit itself to one instance**; One main instance exists, and can be retrieved with `getInstance()`,
but you can still create other instances of your classes, which can be very useful
(exemple: you may have one main connection to your database, but create another for some specific part of your application)

A Component class got those useful methods :
```php
/* Can be modified, gives a default instance of your class when getInstance is called the first time */
public static function getDefaultInstance();

/* Return the global instance of the class, create one with getDefaultInstance if needed */
final public static function getInstance();

/* Replace the global instance with another */
final public static function setInstance(self $newInstance);

/* Remove the global instance if needed (useful to call the destructor) */
final public static function removeInstance();
```



## ✅ Tutorial: Creating a Component

### Context

1. Our application is named `MagicShip`

### Creation

Let's say we want to create a `MagicOrderPrinter`, which is a class that
can print a PDF of an order and log informations about it

First we have to define our class

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

Then to transform it, we only have to use the `Component` trait, and implement `getDefaultInstance`


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
a global instance will be created, and shall log informations to `magic-printer.csv` by default

```php
# Using the default instance
MagicOrderPrinter::getInstance()->printOrder(204987);

# Create another instance for specific situations
$debugPrinter = new MagicOrderPrinter(new Logger("magic-printer-debug.csv"));
$debugPrinter->printOrder(209409);
```


[< Back to summary](../home.md)