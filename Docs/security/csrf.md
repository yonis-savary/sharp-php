[< Back to Summary](../home.md)

# ✅ CSRF

The [`Csrf`](../../Classes/Security/Csrf.php) component allows you to use a [CSRF Token](https://owasp.org/www-community/attacks/csrf) in your application, its usage made through the component class or its helper functions

```php
$csrf = Csrf::getInstance();

// Get an HTML Hidden input containing the token
$csrf->getHTMLInput();
csrfInput(); // Helper

// Directly get the token string
$csrf->getToken();
csrfInput(); // Helper

// Unset the token
// (regenerated the next time getToken() is called)
$csrf->resetToken();

// Check if a request contain the CSRF token
$csrf->checkRequest($requestObject);
```

## Configuration

By default, `Csrf` gives you an HTML Input as is :

```html
<input type="hidden" name="csrf-token" value="<token>">
```

In the case where you need to use the `csrf-token` name for another input, you can edit the default input name in the component configuration


```json
"csrf": {
    "html-input-name": "csrf-token"
}
```

## Making a middleware

Here is an example of a basic Csrf Middleware for your application

```php
use Sharp\Classes\Web\MiddlewareInterface;
use Sharp\Classes\Http\Request;
use Sharp\Classes\Http\Response;
use Sharp\Classes\Security\Csrf;

class CsrfMiddleware implements MiddlewareInterface
{
    public static function handle(Request $request): Request|Response
    {
        if (Csrf::getInstance()->checkRequest($request))
            return $request;

        return Response::json("Invalid CSRF token !");
    }
}

```

[< Back to Summary](../home.md)