[< Back to summary](../home.md)

# 🪝 Events

Sharp got the [`Events`](../../Classes/Core/Events.php) class, which allows you to add event listeners/hooks

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
    $args = $event["args"];
    $results = $event["results"];
});:

# Dispatched when Autoloaded fail to find a class file
$events->on("autoloadFallback", function($event){
    $class = $event["class"]
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

// Triggered when Authentication->login is called
$events->on("authenticatedUser", function($event){
    $user = $event["user"];
});

// Triggered when the Router cannot find a matching route for the request
$events->on("routeNotFound", function($event){
    $request = $event["request"];
    $response = $event["response"];
});

// Triggered when the Router receive an exception while executing a route callback
$events->on("internalServerError", function($event){
    $request = $event["request"];
    $response = $event["response"];
});

// Triggered before Renderer require renderer view
$events->on("beforeBodyViewRender", function($event) {
    $templateName = $event["view"];
});

// Triggered after Renderer required renderer view
//(HTML content can be injected/displayed)
$events->on("afterBodyViewRender", function($event) {
    $templateName = $event["view"];
});

```

[< Back to summary](../home.md)