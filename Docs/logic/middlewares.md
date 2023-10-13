[< Back to summary](../home.md)

# 🚦 Middlewares

Middlewares are classes that are called just before your routes callback,
they tell if a [`Request`](../../Classes/Http/Request.php) can access your route

A middleware is a class that implements [`MiddlewareInterface`](../../Classes/Web/MiddlewareInterface.php),
which got this method :

```php
public static function handle(Request $request) : Request|Response;
```

- Returning a [`Request`](../../Classes/Http/Request.php) mean that your middleware assume the given request has access to the route.
- Returning a [`Response`](../../Classes/Http/Response.php) mean that the access is forbidden and the response is displayed to the user

When creating a route, use the `middlewares` parameter to set which middlewares are applied to the route

```php
Route::get("/my-admin-page", [AdminController:class, "dashboard"], middlewares: [
    AuthMiddleware::class
    AdminMiddleware::class
]);
```

You can also use them in route grouping

```php
$router->addGroup(
    ["middlewares" => [AuthMiddleware::class, AdminMiddleware::class]],
    /* Every routes here got these two middlewares */
);
```

## Need of middleware ?

This section is here to explain the need of middlewares and why they are essential when building an application

Let's imagine that you are building an application, which got an authentication system, your app have a few public routes, the rest is only accessible for logged users

You may want to check if your user is logged in your controller callback:
```php
if (!userIsLogged())
    return Response::redirect("/login");
```

It takes 2 lines of code to redirect any user that is not logged, so far so good

Now, let's assume you have 50 controller methods in your app, it's now 100 lines of code to write ! (or copy-paste)

If, someday, your authentication system change, you now have to go through all your controllers methods and edit 100 times your new code, which is very tedious

Using middlewares, you just have to write your code once, and group your routes with your middleware. And so, the day it changes again, you only have to edit your middleware class.

Also, middlewares can be combined to give different conditions, you can have one to check if the user is authenticated, one to check the CSRF token...etc.

[< Back to summary](../home.md)