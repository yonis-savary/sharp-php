[< Back to summary](../README.md)

# 🪝 Events

Sharp got the [`EventListener`](../../Classes/Core/EventListener.php) class, which allows you to add event listeners/hooks

```php
$events = EventListener::getInstance();

$events->on("log-this", function(CustomEvent $event){
    Logger::getInstance()->debug($event->extra["text"]);
});

$events->dispatch(new CustomEvent("log-this", ["text" => "Hello world"]));
```

## Specific Event Classes

You can make your own event class by making a class that extends from [`AbstractEvent`](../../Classes/Core/AbstractEvent.php)

`MyApp\Classes\Events\ShippedOrder.php`
```php
class ShippedOrder
{
    public function __construct(
        public Order $order
    ){}
}
```

Then you can trigger it by giving `dispatch` an instance of the class

```php
$eventListener->dispatch(new ShippedOrder($order));
```

And listen for it with its classname

```php
$eventListener->on(ShippedOrder::class, function(ShippedOrder $event){
    debug("Sent order N°" . $event->order->id);
});
```

## Framework Base Events

The framework has some base `AbstractEvent` object that are automatically dispatched on certain condition

Base/General events:
- [`BeforeViewRender`](../../Classes/Events/BeforeViewRender.php)
- [`CalledCommand`](../../Classes/Events/CalledCommand.php)
- [`ConnectedDatabase`](../../Classes/Events/ConnectedDatabase.php)
- [`DispatchedEvent`](../../Classes/Events/DispatchedEvent.php)
- [`FailedAutoload`](../../Classes/Events/FailedAutoload.php)
- [`LoadedFramework`](../../Classes/Events/LoadedFramework.php)
- [`RouteNotFound`](../../Classes/Events/RouteNotFound.php)
- [`RoutedRequest`](../../Classes/Events/RoutedRequest.php)
- [`UncaughtException`](../../Classes/Events/UncaughtException.php)

Autobahn's Events:
- [`AutobahnCreateAfter`](../../Classes/Events/AutobahnEvents/AutobahnCreateAfter.php)
- [`AutobahnCreateBefore`](../../Classes/Events/AutobahnEvents/AutobahnCreateBefore.php)
- [`AutobahnDeleteAfter`](../../Classes/Events/AutobahnEvents/AutobahnDeleteAfter.php)
- [`AutobahnDeleteBefore`](../../Classes/Events/AutobahnEvents/AutobahnDeleteBefore.php)
- [`AutobahnMultipleCreateAfter`](../../Classes/Events/AutobahnEvents/AutobahnMultipleCreateAfter.php)
- [`AutobahnMultipleCreateBefore`](../../Classes/Events/AutobahnEvents/AutobahnMultipleCreateBefore.php)
- [`AutobahnReadAfter`](../../Classes/Events/AutobahnEvents/AutobahnReadAfter.php)
- [`AutobahnReadBefore`](../../Classes/Events/AutobahnEvents/AutobahnReadBefore.php)
- [`AutobahnUpdateAfter`](../../Classes/Events/AutobahnEvents/AutobahnUpdateAfter.php)
- [`AutobahnUpdateBefore`](../../Classes/Events/AutobahnEvents/AutobahnUpdateBefore.php)

[< Back to summary](../README.md)