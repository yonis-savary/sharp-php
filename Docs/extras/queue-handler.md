[< Back to Summary](../home.md)

# ⌛️ QueueHandler Trait

Sharp got the `QueueHandler` trait that can be applied to any of your classes

With this class, we can compose simple queues in our application (to send mail for example)

## Usage

To use this trait :
- put `use QueueHandler` to your class
- implements `protected static function processQueueItem(array $data)` in your class, this method purpose is to process ONE item of your queue data
- use `self::pushQueueItem(array $data)` in your base class to push an item to the queue

To process your queue items you can
- call `YourClass::processQueue()` to only process one specific queue
- launch `php do process-queues` to process **every** queue in your application

```php
class MailController
{
    use QueueHandler;

    public static function sendNotification(Request $request)
    {
        self::pushQueueItem($request->params([
            "email",
            "subject",
            "body"
        ]));
    }

    protected static function processQueueItem(array $data)
    {
        $email = $data["email"];
        $subject = $data["subject"];
        $body = $data["body"];

        /* Send mail */
    }
}
```

## Advanced usage

- Every `QueueHandler` stores their items in a `Storage` object, which can be retrieved with
```php
MyClass::getQueueStorage();
```

By default, `processQueue()` will process 10 items in your queue storage,but you can edit this number by re-implementing `getQueueProcessCapacity`
```php
public static function getQueueProcessCapacity(): int
{
    return 25; // Now process 25 item when called
}
```

By default, `QueueHandler` classes log information in the default `Logger` instance, but it can be replaced by re-implementing `getQueueProcessingLogger()`

```php
protected static function getQueueProcessingLogger(): Logger
{
    return new Logger("mail.csv");
}
```


You can also launch `php do clear-queues` to clear your queue directories

[< Back to Summary](../home.md)