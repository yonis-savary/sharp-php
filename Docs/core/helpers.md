[< Back to summary](../home.md)

# 🌐 Code Helpers

Sharp got some helpers files that are included when the framework is loaded.

Those files contains shortcuts to basic framework operation (like adding a route, logging informations...etc)

## Files

This list will describe which file contains which function,
you can click on any file name to see its content

### [`helpers.php`](../../Helpers/helpers.php)
```php
function session          (string $key): mixed;
function sessionSet       (string $key, mixed $value): void;

function cache            (string $key, mixed $default=false): mixed;
function cacheSet         (string $key, mixed $value, int $timeToLive=3600*24): void;

function sharpDebugMeasure(callable $callback, string $label="Measurement"): void;

function buildQuery       (string $query, array $context=[]): string;
function query            (string $query, array $context=[]): array;

function onEvent          (string $event, callable ...$callbacks): void;
function dispatch         (string $event, mixed ...$args): void;
```

### [`helpers-log.php`](../../Helpers/helpers-log.php)
```php
function debug    (mixed ...$messages): void;
function info     (mixed ...$messages): void;
function notice   (mixed ...$messages): void;
function warning  (mixed ...$messages): void;
function error    (mixed ...$messages): void;
function critical (mixed ...$messages): void;
function alert    (mixed ...$messages): void;
function emergency(mixed ...$messages): void;
```

### [`helpers-storage.php`](../../Helpers/helpers-storage.php)
```php
function storePath            (string $path): string;

function storegetSubStorage   (string $path): Storage;
function storeGetStream       (string $path, string $mode="r", bool $autoclose=true): mixed;

function storeWrite           (string $path, string $content, int $flags=0): void;
function storeRead            (string $path): string;

function storeIsFile          (string $path): bool;
function storeIsDirectory     (string $path): bool;

function storeMakeDirectory   (string $path): void;
function storeRemoveDirectory (string $path): bool;
function storeUnlink          (string $path): bool;

function storeExploreDirectory(string $path, int $mode=Storage::NO_FILTER): array;
function storeListFiles       (string $path="/"): array;
function storeListDirectories (string $path="/"): array;
```

### [`helpers-web.php`](../../Helpers/helpers-web.php)
```php
function asset   (string $target): string;
function script  (string $target, bool $inject=false): string;
function style   (string $target, bool $inject=false): string;

function render  (string $templateName, array $vars=[]): string;

function template(string $templateName, array $context=[]);
function section (string $sectionName): ?string;
function start   (string $sectionName): void;
function stop    (): void;
```

### [`helpers-routing.php`](../../Helpers/helpers-routing.php)
```php
function addRoutes    (Route ...$routes): void
function addGroup     (array $group, Route ...$routes): void
function groupCallback(array $group, callable $routeDeclaration): void
function createGroup  (string|array $urlPrefix, string|array $middlewares): array
```

[< Back to summary](../home.md)