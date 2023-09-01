[< Back to summary](../home.md)

# 🔥 Cache

The [`Cache`](../../Classes/Env/Cache.php)
class can save [serialized](https://www.php.net/manual/en/language.oop5.serialization.php)
object for you in any [Storage](./storage.md) object

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

[< Back to summary](../home.md)