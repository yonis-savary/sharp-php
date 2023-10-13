[< Back to summary](../home.md)

# 🔏 Session Management

With Sharp, the session management is really straightforward, it is made
through the [`Session`](../../Classes/Env/Session.php) component

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

# Perform an operation on a key
$session->edit("fail-counter", fn($x=0) => $x+1);

# Set/Replace multiple keys at the same time
$session->merge([
    "logged" => 0,
    "fail-counter" => 0
]);
```

> [!NOTE]
> `Session` class is bound to `$_SESSION` by reference ! Which mean that you can edit `$_SESSION` and still get your data with the `Session` component

[< Back to summary](../home.md)