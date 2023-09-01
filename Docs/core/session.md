[< Back to summary](../home.md)

# 🔏 Session Management

With Sharp, the session management is really straightforward, it is made
through [Session](../../Classes/Env/Session.php)


```php
$session = Session::getInstance();

# Set is used to edit data in the session
$session->set("user", $myUserObject);

# Get is used to read data (a default value can be given)
$organization = $session->get("organization", $default);

# Try is an alias to get(x, false), which can be used in conditions
if ($rights = $session->try("rights"))
{
    // Rights exists and are stored inside $rights
}

# Has check if given keys are all present at the same time
$hasPrivileges = $session->has("privileges");

# Unset remove some data from the session
$session->unset("temp-file");
```


Also, two shortcuts are present in [Helpers/helpers.php](../../Helpers/helpers.php) to help you
read/write data from the session

```php
session("myKey"); // Alias to Session::getInstance()->get()
sessionSet("myKey", $myValue) // Alias to Session::getInstance()->set()
```


[< Back to summary](../home.md)