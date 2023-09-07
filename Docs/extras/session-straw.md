[< Back to summary](../home.md)

# 🥤 Session Straw

We all know it, using global variables is quite a bad habit,
still, you sometimes have to work with a piece of data that you need in all your business code

Let's say you want to store your user permissions and access it any time,
the first solution that comes to mind is to use the session

```php
# Constant allow us to have autocompletion in the IDE
const USER_PERMISSION = "session.user.permission";

$session = Session::getInstance();

$session->set(USER_PERMISSION, [1,2,3]);

$permissions = $session->get(USER_PERMISSION);
```

It is readable, but not very pratical, we can do better,
with the [Session Straw](../../Classes/Extras/SessionStraw.php) trait

This trait is simply a way to transform a class into a global getter-setter

```php
class UserPermission { use SessionStraw; }

UserPermission::set([1,2,3]);

$permission = UserPermission::get();
```

This way, you have a global variable that is stored in a namespace and
can be accessed with two words

### Creating a straw

To create a straw, you have two choices
- Manually writing it by creating a file then the class
- Using the `create-straw` command

```bash
# The command will prompt you to type a straw name
php do create-straw

# You can also directly give it names
php do create-straw UserId UserPermissions
```

[< Back to summary](../home.md)