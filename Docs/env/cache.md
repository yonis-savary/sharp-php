[< Back to summary](../home.md)

# 🔥 Cache

The [`Cache`](../../Classes/Env/Cache.php)
class can save [serialized](https://www.php.net/manual/en/language.oop5.serialization.php)
objects in any [`Storage`](./storage.md)

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

## Advanced - Working with references

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
can always be retrieved with `Cache->get`

[< Back to summary](../home.md)