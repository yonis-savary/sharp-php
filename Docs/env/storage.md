[< Back to summary](../home.md)

# 📁 Storage

[Storage](../../Classes/Env/Storage.php) is a class which represent a file directory

## Usage

```php
$storage = Storage::getInstance();

// Return the Storage root directory absolute path
$storage->getRoot();

// Return an absolute path to the target
$storage->path("MyDirectory");

$storage->write("MyDirectory/file.txt", "Hello");
$storage->write("MyDirectory/file.txt", "Hello", FILE_APPEND);
$storage->read("MyDirectory/file.txt");

$storage->isDirectory("MyDirectory/SubDir");
$storage->isFile("MyDirectory/file.txt");

$storage->makeDirectory("MyDirectory/SubDir");
$storage->removeDirectory("MyDirectory/SubDir");
$storage->unlink("MyDirectory/file.txt");

# `exploreDirectory()` recursively explore a directory and return
# a list of absolute path depending the given filter
$storage->exploreDirectory("MyDirectory");
$storage->exploreDirectory("MyDirectory", Storage::NO_FILTER);
$storage->exploreDirectory("MyDirectory", Storage::ONLY_DIRS);
$storage->exploreDirectory("MyDirectory", Storage::ONLY_FILES);

# Return a list of direct dirs/files in the root directory (no subdirectory)
$storage->listFiles();
$storage->listDirectories();

// Throws an exception if the directory is not writable
$storage->assertIsWritable();

// Get a new Storage object from a subdirectory
$storage->getSubStorage("MySubDir");

// Get a resource/stream object
$storage->getStream("MyDirectory/output.txt", "a");
```

[< Back to summary](../home.md)