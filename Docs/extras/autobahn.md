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

**(Note, the returned data format is described in the [`Database & Model documentation`](../data/database.md))**

### 🟣 UPDATE - update ONE user by using its primary key

In our example, not giving `id` in the request will return an error

`PUT /user {id: 5, login: 'mike'}` or `PATCH /user {id: 5, login: 'mike'}` will give
```sql
UPDATE user SET login = 'dale' WHERE id = 5
```

### 🔴 DELETE - delete multiples users with body as filters

`DELETE /user {login: 'mike'}` will give
```sql
DELETE FROM user WHERE login = 'mike'
```

By default, dangerous queries with no parameters are blocked, but you can enable them by
setting `autobahn.prevent-dangerous-delete` to `false`


[< Back to summary](../home.md)