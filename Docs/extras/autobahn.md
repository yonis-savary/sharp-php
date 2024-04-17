[< Back to summary](../README.md)

# 🚘 Automatic CRUD API

Great news ! No need to write basic CRUD API for your models anymore !

Sharp got the [`Autobahn`](../../Classes/Extras/Autobahn.php) component, which can
create CRUD API routes for any of your model

```php
$autobahn = Autobahn::getInstance();

# CREATE route
$autobahn->create(User::class);
# READ route
$autobahn->read(User::class);
# UPDATE route
$autobahn->update(User::class);
# DELETE route
$autobahn->delete(User::class);

# Shortcut to call everything above
$autobahn->all(User::class);
```

For this example, let's say we have called `$autobahn->all(User::class)`
to create four routes for the `User`, which points to the `user` table, and got those fields :
- id (PK)
- login
- password

Every created route has the `/user` route, but their HTTP methods differs

**(json body behind any route represent the request's body)**

## 🟢 CREATE

**Create a user with given fields**

```http
POST /user {login: 'bob', password: 'mike'}
```

will execute

```sql
INSERT INTO user (login, password) VALUES ('bob', 'mike');
```

## 🔵 READ

**Read a user with given fields as SQL condition**

```http
GET /user {login: 'bob', id: 2}
```

will execute

```sql
SELECT ... FROM user WHERE login = 'bob' AND id = 2
```

**We can also use arrays**


```http
GET /user {login: 'bob', id: [2,3]}
```

will execute

```sql
SELECT ... FROM user WHERE login = 'bob' AND `id` IN ('2','3')
```

> [!NOTE]
> - You can put `_join` (`true|false`) in your request to (dis/en)able model foreign keys exploration
> - You can also set `_ignores` (`string|array`) to ignores some foreign keys in the model foreign keys exploration
>     - Example: `ignores = ['user&fk_type']` will ignore any foreign key that pass through the `fk_type` field of the `user` table
> - The returned data format is described in the [`Database Query documentation`](../data/database-query.md.md)

## 🟣 UPDATE

**Update ONE user by using its primary key**

In our example, not giving `id` in the request will return an error

```http
PUT|PATCH /user {id: 5, login: 'mike'}
```

will execute

```sql
UPDATE user SET login = 'dale' WHERE id = 5
```

**We can also use arrays**

```http
PUT|PATCH /user {id: [5, 76], login: 'mike'}
```

will execute

```sql
UPDATE user SET login = 'dale' WHERE id IN ('5', '76')
```

## 🔴 DELETE

**Delete multiples users with body as filters**

```http
DELETE /user {login: 'mike'}
```

will execute

```sql
DELETE FROM user WHERE login = 'mike'
```
```http
DELETE /user {login: ['mike', 'bob']}
```

 will execute

```sql
DELETE FROM user WHERE `login` IN ('mike', 'bob')
```

> [!IMPORTANT]
> Dangerous queries with no parameters are blocked

## Additional Properties

### Grouping

`Autobahn` uses `Router->addRoute()`, which mean that you can group Autobahn routes !

```php
$autobahn->all(User::class);
// Routes are "/user"

Router::getInstance()->groupCallback(
    ["path" => "api"],
    function($router){
        $autobahn = Autobahn::getInstance();
        $autobahn->all(User::class);
        // Routes are "/api/user"
    }
);
```

### Autobahn Middlewares

Every Autobahn callback can take some middlewares to protect your queries

For Read, Update and Delete callback, the `DatabaseQuery` that is about to be executed is given to your middleware, which mean that your can edit it

```php
$autobahn->read(User::class, function(DatabaseQuery &$query){
    $query->where("fk_user", UserId::get());
});
```

With this middleware, we make sure the user can only read its own profile data

For Insert callback, your middleware can take data that are about to be inserted

```php
$autobahn->read(UserData::class, function(array &$data){
    $data["fk_user"] = UserId::get();
});
```

With this middleware, we make sure that the user can only insert data about its own profile

## 🟩 CREATE MULTIPLE route

An additional route is created by Autobahn, it is a "create-multiple" route that can be used to insert multiple rows at once in your model table

```http
POST /user/create-multiples
[
    {login: 'andy', password: 'lucy'},
    {login: 'lucy', password: 'andy'},
    {login: 'dale', password: 'coffee'}
]
```

will execute
```sql
INSERT INTO user (login, password) VALUES ('andy', 'lucy'), ('lucy', 'andy'), ('dale', 'coffee');
```


## Configuration

`Autobahn` is based on a "driver" which implements [`DriverInterface`](../../Classes/Extras/AutobahnDrivers/DriverInterface.php),
it is the driver that defines the API behavior, which means that you can change the API behind `Autobahn` at any moment

If you decide to use one, the only step required is to specify it in the configuration:

```json
"autobahn": {
    "driver": "Sharp\\Classes\\Extras\\AutobahnDrivers\\BaseDriver"
}
```

[< Back to summary](../README.md)