[< Back to summary](../home.md)

# 🚘 Automatic CRUD API with `Autobahn`

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

## Routes description

For this example, let's say we have called `$autobahn->all(User::class)`
to create four routes for the `User`, which points to the `user` table, and got those fields :
- id (PK)
- login
- password

Every created route has the `/user` route, but their methods differs

**(json body behind any route represent the request's body)**

### 🟢 CREATE - create a user with given fields

`POST /user {login: 'bob', password: 'mike'}` will give
```sql
INSERT INTO user (login, password) VALUES ('bob', 'mike');
```

### 🔵 READ - read a user with given fields as SQL condition

`GET /user {login: 'bob', id: 2}` will give
```sql
SELECT ... FROM user WHERE login = 'bob' AND id = 2
```

`GET /user {login: 'bob', id: [2,3]}` will give
```sql
SELECT ... FROM user WHERE login = 'bob' AND `id` IN ('2','3')
```

Note:
- You can put `_join` (`true|false`) in your request to dis/enable model foreign keys exploration
- You can also set `_ignores` (`string|array`) to ignores some foreign keys in the model foreign keys exploration
(example: `ignores = ['user&fk_type']` will ignore any foreign key that pass through the `fk_type` field of the `user` table)
- The returned data format is described in the [`Database & Model documentation`](../data/database.md))

### 🟣 UPDATE - update ONE user by using its primary key

In our example, not giving `id` in the request will return an error

`PUT /user {id: 5, login: 'mike'}` or `PATCH /user {id: 5, login: 'mike'}` will give
```sql
UPDATE user SET login = 'dale' WHERE id = 5
```
`PUT /user {id: [5, 76], login: 'mike'}` or `PATCH /user {id: [5, 76], login: 'mike'}` will give
```sql
UPDATE user SET login = 'dale' WHERE id IN ('5', '76')
```

### 🔴 DELETE - delete multiples users with body as filters

`DELETE /user {login: 'mike'}` will give
```sql
DELETE FROM user WHERE login = 'mike'
```
`DELETE /user {login: ['mike', 'bob']}` will give
```sql
DELETE FROM user WHERE `login` IN ('mike', 'bob')
```

Note: dangerous queries with no parameters are blocked


## Additional Properties

### Grouping

`Autobahn` use the `addRoute` method of `Router`, which mean that you can group Autobahn routes !

```php
Router::getInstance()->groupCallback([
    "path" => "api"
], function($router){
    $autobahn = Autobahn::getInstance();
    $autobahn->all(User::class);
});
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



[< Back to summary](../home.md)