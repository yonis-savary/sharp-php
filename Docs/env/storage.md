[< Back to summary](../README.md)

# 📁 Storage

[Storage](../../Classes/Env/Storage.php) is a class which represent a file directory, it can be used
to read, write and explore the directory content

> [!NOTE]
> The default `Storage` object points to the `Storage` directory inside your project root

## Usage

### Basic operations

```php
// Get the default storage
$storage = Storage::getInstance();

// Storage creation, create the directory if inexistent
$storage = new Storage("/home/foo/some-directory/");

// Return the Storage root directory (absolute path)
$storage->getRoot();

// Return an absolute path to the target
$storage->path("MyDirectory");
```

### File operations

```php
$storage->read("MyDirectory/file.txt");

$storage->write("MyDirectory/file.txt", "Hello");
// file_put_contents() flags can be given
$storage->write("MyDirectory/file.txt", "Hello", FILE_APPEND);

$storage->isDirectory("MyDirectory/SubDir");
$storage->isFile("MyDirectory/file.txt");

// Make a directory and its parent if inexistent
$storage->makeDirectory("MyDirectory/SubDir");
$storage->removeDirectory("MyDirectory/SubDir");
$storage->unlink("MyDirectory/file.txt");

// Get a resource/stream object

// This stream will be closed automatically when the Storage is destructed
$stream = $storage->getStream("MyDirectory/output.txt", "a");

// This stream needs to be manually closed
$input = $storage->getStream("MyDirectory/input.txt", "a", false);
fclose($input);
```

### Directory listing & exploration

```php
# `exploreDirectory()` recursively explore a directory and return
# a list of absolute file/directory paths (depending the given filter)
$storage->exploreDirectory("MyDirectory");
$storage->exploreDirectory("MyDirectory", Storage::NO_FILTER);
$storage->exploreDirectory("MyDirectory", Storage::ONLY_DIRS);
$storage->exploreDirectory("MyDirectory", Storage::ONLY_FILES);

# Return a list of direct directories/files in the root directory (no subdirectory)
$storage->listFiles();
$storage->listDirectories();

```

### Additional features

```php
// Throws an exception if the directory is not writable
// Automatically called when calling write() method
$storage->assertIsWritable();

// Get a new Storage object from a sub-directory
$storage->getSubStorage("MySubDir");
```

## FTP Storage

Sharp supports FTP connections ! They are made through the [FTPDriver](../../Classes/Env/Drivers/FTPDriver.php)
class which is a basic wrapper for FTP

They can be given to the `Storage` constructor

```php
$connection = new FTPDriver("somewebsite.com", "username", "password");
$storage = new Storage("/home/root/Documents/MyAppStorage", $connection);
```

> [!IMPORTANT]
> `$storage->openFile()` cannot be used as FTPDriver don't support stream/resources objects



[< Back to summary](../README.md)