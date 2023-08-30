[< Back to summary](../home.md)

# Sharp-PHP - Working with database and models


## Using the database

Before using your database, you have to configure its connection

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

Note: the default database config is `driver=mysql, host=localhost, port=3306, user=root`, so you only have to
configure `database` and `password`

Then, your database usage is done through three main methods

```php
$db = Database::getInstance();

# Used to build a query string
$query = $db->build("SELECT id FROM ship WHERE name = {}", ["PHP Bounty"]);

# Used to directly fetch rows
$results = $db->query("SELECT id FROM ship WHERE name = {}", ["Above the code"]);

$id = $db->lastInsertId();

```

### Additionnal properties

```php
$db = Database::getInstance();

$db->hasTable("ship_order");
$db->hasField("ship_order", "fk_ship");
```


## Interacting with models

Sharp philosophy on models is that: your application don't have to dictate how your database
schema should look like, it is your application that must adapt itself to your structure

Models in Sharp are very simple; A model is a class that use the
[`Sharp\Classes\Data\Model`](../../Classes/Data/Model.php) trait

The goal of sharp is to avoid writting manually any model, they can be generated automatically

### Generating models

To generate your models, you first have to configure your database connection, when it's done,
launch this in your terminal

```bash
php do fetch-models
```

This will create models classes in `YourApp/Models`
(Note: SQL adapters transforms `snake_case` names to `PascalCase`)

### Interaction

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
User::getFields(); // Return an array of DatabaseField object
User::getFieldNames(); // Return ["id", "login", "password", "salt"]
User::getInsertables(); // Return ["login", "password", "salt"]

User::insert(); // Return a DatabaseQuery object ready to insert inside user table
User::select(); // Return a DatabaseQuery object ready to select from user table
User::update(); // Return a DatabaseQuery object ready to update user table
User::delete(); // Return a DatabaseQuery object ready to delete from user table

# Some examples

$users = User::select()->where("fk_country", 14)->whereSQL("creation_date > DATESUB(NOW(), INTERVAL 3 MONTH)")->limit(5)->fetch();

$someUser = User::select()->where("id", 168)->first();

User::update()->set("fk_type", 2)->where("fk_type", 5)->first();

User::delete()->whereSQL("fk_type IN (1, 12, 52, 4)")->order("id", "DESC")->fetch();
```