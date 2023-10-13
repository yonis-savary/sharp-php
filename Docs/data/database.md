[< Back to summary](../home.md)

# 📚 Database and models

Database connection is made through the [`Database`](../../Classes/Data/Database.php) component (which uses `PDO`)

## Using the database

Before using the database, we have to configure its connection

```json
"database": {
    "driver": "mysql",
    "database": "shipping_app",
    "host": "localhost",
    "port": 3306,
    "user": "root",
    "password": "sh1pp1ng_1s_gre@t"
}
```

> [!NOTE]
> The default database config is `driver=mysql, host=localhost, port=3306, user=root`, so you only have to configure `database` and `password` if working on a local MySQL database

Then, your database usage is done through three main methods

```php
$db = Database::getInstance();

# Used to build a query string
$query = $db->build(
    "INSERT INTO ship (name) VALUES ({})",
    ["PHP Bounty"]
);

# Used to directly fetch rows
$results = $db->query($query);

$id = $db->lastInsertId();
```

### Additional Database Properties

```php
// build() binding can take arrays of data
$results = $db->query(
    "SELECT id FROM ship WHERE name IN {}",
    [["Above the code", "PHP Bounty"]]
);

// Check if a table exists (return true/false)
$db->hasTable("ship_order");

// Check if a field in a table exists (return true if both exists)
$db->hasField("ship_order", "fk_ship");
```

### Working with SQLite

`Database` also support SQLite connections ! Here is an example of configuration

```json
"database": {
    "driver": "sqlite",
    "database": "myDatabase.db",
    "enable-foreign-keys": true
}
```

This config will create a `Storage/myDatabase.db` file with your data inside

## Interacting with models

Sharp philosophy on models is
> Your application don't have to dictate how your database schema should look like
>
> It is your application that must adapt itself to your structure

A Model in Sharp is a class that use the
[`Sharp\Classes\Data\Model`](../../Classes/Data/Model.php) trait

The goal of sharp is to avoid writting manually any model, they can be generated automatically

### Generating models

To generate your models, launch this in your terminal

```bash
php do fetch-models
```

This will create models classes in `YourApp/Models`, with `snake_case` names transformed their `PascalCase` equivalent

> [!NOTE]
> So far, only two types of database are supported :
> - MySQL (+MariaDB)
> - SQLite
>
> A new adapter can be created by implementing a new `GeneratorDriver`

### Model Interaction

Let's say we have a `User` model which got this structure:
```sql
CREATE TABLE user (
    id INT PRIMARY KEY AUTO_INCREMENT,
    login VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    salt VARCHAR(100) NOT NULL
);
```

Here is how we can interact with the model

```php
User::getTable(); // Return "user"
User::getPrimaryKey(); // Return "id"
User::getFields(); // Return an array as `FieldName => DatabaseField` object
User::getFieldNames(); // Return ["id", "login", "password", "salt"]
User::getInsertables(); // Return ["login", "password", "salt"]

User::insert(); // Return a DatabaseQuery object ready to insert inside user table
User::select(); // Return a DatabaseQuery object ready to select from user table
User::update(); // Return a DatabaseQuery object ready to update user table
User::delete(); // Return a DatabaseQuery object ready to delete from user table

# Some examples

$users = User::select()
->where("fk_country", 14)
->whereSQL("creation_date > DATESUB(NOW(), INTERVAL 3 MONTH)")
->limit(5)->fetch();

$someUser = User::select()
->where("id", 168)
->first();

// Same as the previous query
$someUser = User::findId(168);

User::update()
->set("fk_type", 2)
->where("fk_type", 5)
->first();

User::delete()
->whereSQL("fk_type IN (1, 12, 52, 4)")
->order("id", "DESC")
->fetch();
```

To know more on `DatabaseQuery`, you can read [its documentation](./database-query.md)

[< Back to summary](../home.md)