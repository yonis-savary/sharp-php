[< Back to summary](../home.md)

# 🪝 Events

Sharp got the [`Events`](../../Classes/Core/Events.php) class, which allows you
to add event listeners

```php
$events = Event::getInstance();

$events->on("log-this", function(...$messages){
    Logger::getInstance()->debug(...$messages);
});

$events->dispatch("log-this", "Hello world");
```

[< Back to summary](../home.md)