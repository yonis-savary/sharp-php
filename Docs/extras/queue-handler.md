[< Back to Summary](../README.md)

# ⌛️ QueueHandler Trait

Sharp got the `QueueHandler` trait that can be applied to any of your classes

With this class, we can compose simple queues in our application

## Usage

To use this trait :
- put [`use QueueHandler`](../../Classes/Extras/QueueHandler.php) to your class
- implements `protected static function processQueueItem(array $data): bool` in your class, this method purpose is to process ONE item of your queue data (return true if processed, false is skipped)
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

    protected static function processQueueItem(array $data): bool
    {
        $email = $data["email"];
        $subject = $data["subject"];
        $body = $data["body"];

        /* Send mail */
        return true;
    }
}
```


## Properties

The purpose of the `QueueHandler` trait is to process `n` items (defined by `getQueueProcessCapacity()`) each calling,
if `processQueueItem()` returns `false`, it means that the given item was skipped (or "did not count"), in this case
the first item is not counted in the processing

With this method, we can be sure that we are processing `n` "true" items each calling


With `n=5`
```
Item 0 - Skipped
✓ Item 1 - Processed i=1
Item 2 - Skipped
Item 3 - Skipped
✓ Item 4 - Processed i=2
Item 5 - Skipped
✓ Item 6 - Processed i=3
✓ Item 7 - Processed i=4
✓ Item 8 - Processed i=5, stops here
- Item 9
```

Items `0,2,3` were skipped, but `1,4,6,7,8` were processed, which means that we DID processed `n` items

(If the number of items is insufficient, the processing is stopped the same way as `n` was reached)

## Advanced usage

- Every `QueueHandler` stores their items in a `Storage` object, which can be retrieved with
```php
MyClass::getQueueStorage();
```

By default, `processQueue()` will process 10 items in your queue storage, but you can edit this number by re-implementing `getQueueProcessCapacity`
```php
public static function getQueueProcessCapacity(): int
{
    return 25; // Now process 25 item when called
}
```

By default, `QueueHandler` classes logs information in the default `Logger` instance, but it can be replaced by re-implementing `getQueueProcessingLogger()`

```php
protected static function getQueueProcessingLogger(): Logger
{
    return new Logger("mail.csv");
}
```

You can also launch `php do clear-queues` to clear your queue directories, and `php do list-queues -l` to list each queues items

[< Back to Summary](../README.md)