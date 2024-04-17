
[< Back to summary](../README.md)

# Utils

The [`Utils`](../../Core/Utils.php) class got some utilitaries method to simplify some tasks, here are some of it


```php
# Check if a class (or object) uses a specific trait
# (return false if the first parameter is not a valid class/object)
Utils::uses($objectOrClass, 'MyApp\\MyTrait'); # true/false

# Check if a class (or object) implements a specific interface
# (return false if the first parameter is not a valid class/object)
Utils::implements($objectOrClass, 'MyApp\\MyInterface'); # true/false

# Check if a class (or object) extends from another class
# (return false if the first parameter is not a valid class/object)
Utils::extends($objectOrClass, $parent); # false / true


# Normalize a path to
# - only use slashes (anti-slashes are replaced)
# - prevent "empty slashes"
Utils::normalizePath("path\\that/is//made\\of\\anything");
# returns "path/that/is/made/of/anything"

# Join two parts of a path (result is normalized)
# no need to check if parts begin/ends with a slash or not
# (result is normalized with normalizePath)
Utils::joinPath("path/", "/that\\is", "//made/of/", "anything");
# returns "path/that/is/made/of/anything"

# Returns a absolute path from a relative path (relative to the second argument, if not given, relative to the project root)
# (result is normalized)
Utils::relativePath($path, $relativeTo);


# Transform a path into a namespace (used by the autoloader)
# (Given path can be absolute it will be converted into a relative one)
# (result is normalized)
Utils::pathToNamespace($path);

# Does the opposite of pathToNamespace()
# Transform a path into a "correct" namespace
Utils::classnameToPath(string $classname);



# recursively explore a directory and return a list of subdirectories/files
Utils::exploreDirectory($path);
# only return a list of directory names
Utils::exploreDirectory($path, Utils::ONLY_DIRS);
# only return a list of file names
Utils::exploreDirectory($path, Utils::ONLY_FILES);


# List the files inside a directory (does not list files inside subdirectories)
Utils::listFiles($directory);

# List the directories inside a directory (does not list subdirectories)
Utils::listDirectories($directory);

# Check if a byte (needle) is present inside a value (haystack)
Utils::valueHasFlag($haystack, $needle);
# (0b1000, 0b0001) => false
# (0b1010, 0b1001) => false
# (0b1000, 0b0001) => true
# (0b1010, 0b1000) => true
# (0b1010, 0b1010) => true
# (0b1111, 0b1010) => true

# Check if an array is associative
# (An empty array is not considered as associative)
Utils::isAssoc($array);

# Make an array of the value
# If the value is an associative array, make an array of given element
# If the value is a list, does not make any change
Utils::toArray($value);

# Check if "environment" is set to "production" inside your configuration
Utils::isProduction();
Utils::isProduction($someConfiguration);

# Check if a specified application is present inside the "applications" key inside given configuration (global configuration is not specified)
Utils::isApplicationEnabled($application);
Utils::isApplicationEnabled($application, $someConfiguration);
```

[< Back to summary](../README.md)