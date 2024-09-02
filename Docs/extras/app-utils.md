[< Back to Summary](../README.md)

# 🗂 App Utils (AppStorage, AppCache...)

Traits inside [Sharp\Classes\Utils](../../Classes/Utils/) are designed
to help you through the making of your application

Their goal is to provide some global data-container to your app, which are

| Type    | Trait        | Command          |
|---------|--------------|------------------|
| Cache   | `AppCache`   | `create-cache`   |
| Map     | `AppMap`     | `create-map`     |
| Storage | `AppStorage` | `create-storage` |

## Properties

- Every Storage, Cache, Map path is isolated by its namespace/classname
- If one of your storage namespace/classname change, its path shall change too
- `AppMap` uses serialization to store its data !*
- The `get` method always return a reference

## Example: AppStorage for users profile pictures

In this example, our `App` must have a special directory to store user profile pictures

We can manually define a path and create a `Storage` object, 
or create a class that uses the `AppStorage` trait

We can create this class by using the `create-storage` command 

```bash
php do create-storage ProfilePicture
```

Generated code
```php
namespace App\Classes\App\Storages;

use Sharp\Classes\Utils\AppStorage;

class ProfilePicture 
{
    use AppStorage;
} 
```

To use this storage, call the `get` function of the trait, which returns a reference to the global instance of the storage

```php
$storage = ProfilePicture::get();
$theSameStorage = ProfilePicture::get();
$alwaysTheSame = $storage;

// Once we get the instance, we can use it as common Storage object
$storage->write("foo.txt", "Hello!");

$theSameStorage->read("foo.txt") // Hello!
```

[< Back to Summary](../README.md)