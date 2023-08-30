[< Back to summary](../home.md)

# Sharp components

Some classes/features need to be accessed globally from your application (like `Database`, `Logger`...etc)
to resolve this, the [`Component`](../../Classes/Core/Component.php) trait was created.

This trait purpose is to be a [Singleton](https://en.wikipedia.org/wiki/Singleton_pattern) that
does not limit itself to one instance, one main instance exists, and can be retrieved with `getInstance()`,
but you can create another instances of your classes, which can be quite useful (exemple: you can have one main
connection to your database, but create another for some specific part of your application)

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