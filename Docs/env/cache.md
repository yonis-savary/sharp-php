[< Back to summary](../README.md)

# 🔥 Cache

The [`Cache`](../../Classes/Env/Cache.php) class can save [serialized](https://www.php.net/manual/en/language.oop5.serialization.php) objects in any [`Storage`](./storage.md)

```php
$cache = Cache::getInstance();

// Save an object in the Storage
$cache->set("my-key", $anyObjectOfYours, Cache::HOUR * 3);
$cache->set("permanent-object", [1,2,3], Cache::PERMANENT);

// Retrieve/load the serialized object
$anyObjectOfYours = $cache->get("my-key");
$anyObjectOfYours = $cache->get("my-key", $anyDefaultValue);

if ($serialized = $cache->try("another-key"))
{
    // It exists and is loaded !
}

$exists = $cache->has("my-key");

$cache->delete("key-to-delete");
```

## Working with references

Let's say you have a component that need to cache some data to work faster

The first solution is to read the cache and set it again when editing data

```php
public function initialize()
{
    $this->data = Cache::getInstance()->get("my-key", []);
}

public function setData(mixed $data)
{
    $this->data = $data;
    Cache::getInstance()->set("my-key", $this->data);
}
```

But it can be quite hard to read, the ideal solution would be
to get a reference to the cache, that we can directly edit

```php
public function initialize()
{
    $this->data = &Cache::getInstance()->getReference("my-key");
}

public function setData(mixed $data)
{
    $this->data = $data;
}
```

This is the same as the first solution, the element will be saved on destruct and
can always be retrieved with `Cache->get()`

## Advanced features

```php
// Get a Storage object pointing to the Cache directory
$myCache->getStorage();

// Get existing (and not expired) keys in the cache
$myCache->getKeys();

// Get another Cache object pointing into a subdirectory of the parent cache
$myCache->getSubCache();
```

### Clearing the Cache

We have two way to clear the cache :
- Manually deleting the cache files (stored in `Storage/Cache`)
- Launching the `clear-cache` command

Launching `php do clear-cache` will delete every cache item BUT NOT the permanent ones,
to delete them too, launch `php do clear-cache --all`


[< Back to summary](../README.md)