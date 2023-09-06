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


## Framework Base Events

The framework has some base events that are automatically dispatched

```php
$events = Events::getInstance();

# Dispatched after invoking a command with "php do"
$events->on("calledCommand", function($event){
    $command    = $event["command"];
    $name       = $event["name"];
    $origin     = $event["origin"];
    $identifier = $event["identifier"];
    $returned   = $event["returned"];
});


# Dispatched after any event dispatch
# `results` are the callbacks return values
$events->on("dispatchedEvent", function($event){
    $event = $event["event"];
    $args  = $event["args"];
    $results  = $event["results"];
});:


# Dispatched when Autoloaded fail to find a class file
$events->on("autoloadFailed", function($event){
    $class= $event["class"]
});


# Dispatched when both the framework and applications are loaded
$events->on("frameworkLoaded", function(){
    /* No argument given */
});


# Dispatched before Autobahn insert a row
$events->on("autobahnCreateBefore", function($event){
    $model = $event["model"];
    $query = $event["query"];
});

# Dispatched after Autobahn insert a row
$events->on("autobahnCreateAfter", function($event){
    $model = $event["model"];
    $query = $event["query"];
    $insertedId = $event["insertedId"];
});

# Dispatched before Autobahn read row(s)
$events->on("autobahnReadBefore", function($event){
    $model = $event["model"];
    $query = $event["query"];
});

# Dispatched after Autobahn read row(s)
# `results` is the read rows
$events->on("autobahnReadAfter", function($event){
    $model = $event["model"];
    $query = $event["query"];
    $results = $event["results"];
});

# Dispatched before Autobahn update row(s)
$events->on("autobahnUpdateBefore", function($event){
    $model = $event["model"];
    $query = $event["query"];
});

# Dispatched after Autobahn update row(s)
$events->on("autobahnUpdateAfter", function($event){
    $model = $event["model"];
    $query = $event["query"];
});

# Dispatched before Autobahn delete row(s)
$events->on("autobahnDeleteBefore", function($event){
    $model = $event["model"];
    $query = $event["query"];
});

# Dispatched after Autobahn delete row(s)
$events->on("autobahnDeleteAfter", function($event){
    $model = $event["model"];
    $query = $event["query"];
});

```



[< Back to summary](../home.md)