[< Back to summary](../home.md)

# 📦 Storage

[Storage](../../Classes/Env/Storage.php) is a representation of a file directory

## Usage

```php
$storage = Storage::getInstance();

// Return an absolute path to the target
$storage->path("MyDirectory");
// Return the Storage root directory absolute path
$storage->getRoot();

// Write to a file (create the directory if inexistant)
$storage->write("MyDirectory/file.txt", "Hello");
$storage->write("MyDirectory/file.txt", "Hello", FILE_APPEND);
// Read the target file and return the content
$storage->read("MyDirectory/file.txt");

// Check if the directory exists
$storage->isDirectory("MyDirectory/SubDir");
// Check if the file exists
$storage->isFile("MyDirectory/file.txt");

// Get a new Storage object from a subdirectory
$storage->getNewStorage("MySubDir");
// Get a resource/stream object
$storage->getStream("MyDirectory/output.txt", "a");

$storage->removeDirectory("MyDirectory/SubDir");
$storage->unlink("MyDirectory/file.txt");

$storage->makeDirectory("MyDirectory/SubDir");

# `exploreDirectory()` recursively explore a directory and return
# a list of absolute path depending the given filter

# No filter return both directories and files
$storage->exploreDirectory("MyDirectory", Storage::NO_FILTER);
# ONLY_DIRS return only directories names
$storage->exploreDirectory("MyDirectory", Storage::ONLY_DIRS);
# ONLY_FILES return only files names
$storage->exploreDirectory("MyDirectory", Storage::ONLY_FILES);

# Return a list of direct dirs/files in the root directory (no subdirectory)
$storage->listFiles();
$storage->listDirectories();

// Throws an exception if the directory is not writable
$storage->assertIsWritable();
```

[< Back to summary](../home.md)