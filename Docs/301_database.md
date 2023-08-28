[< Back to summary](./000_sharp.md)

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