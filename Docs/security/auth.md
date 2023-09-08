[< Back to summary](../home.md)

# 🔐 Authentication

Sharp got the [`Auth`](../../Classes/Security/Auth.php) class to handle authentication

Authentication is made through [Models](../data/database.md)

## Configuration

You have to configure those five parameters in your configuration:

```json
"auth": {
    "model": "App\\Models\\User",
    "login-field": "login",
    "password-field": "password",
    "salt-field": "salt",
    "session-duration": 3600
}
```

- `model` is the full namespace to your model class
- `login-field` is the name of the unique field in your model
- `password-field` is the name of the field where your password hash is stored
- `salt-field` (optionnal, can be `null`) is the name of the field where your password salt is stored
- `session-duration` duration of Auth session in seconds (for example, 3600sec <=> 1 hour, after one hour of inactivity, the user is logged out)

## Usage

```php
$auth = Auth::getInstance();

// attempt() tries to log the user
// return true on success, false on failure
if ($auth->attempt("login", "password"))
{
    // Success !
}

$auth->isLogged();

// Array of data if the user is logged, null otherwise
$user = $auth->getUser();

// Logout the user as reset attempt number
$auth->logout();

// Number of failed attempts number (reseted when logged in)
$auth->attemptNumber();
```


[< Back to summary](../home.md)