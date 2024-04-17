[< Back to summary](../README.mdmd)

# 📜 Database Queries

Database queries can be made through:
- The `query` method of any `Database` object
- The `DatabaseQuery` object, which modelize basic queries type

> [!NOTE]
> `DatabaseQuery` objects are mostly created through Models shortcuts,
> its manual creation is more an edge case than something else

## INSERT query

The only type of insert that is supported is `INSERT INTO ... VALUES ...`

```php
$query = new DatabaseQuery("user_data", DatabaseQuery::INSERT);
$query->setInsertField(["fk_user", "data"]);
$query->insertValues([1, "one-row"], [2, "another-one"]);

$res = $query->fetch();
$sql = $query->build();
```

## SELECT query

```php
# First Solution (with a model)
$query = User::select();

# Second solution
$query = new DatabaseQuery("user", DatabaseQuery::SELECT);
$query->exploreModel(User::class);


# Manipulation
$query->where("my_field", 5) ;
$query->where("my_field", null, "<>");
$query->whereSQL("creation_date > {}", ['2023-01-01']);
$query->order('user', 'creation_date', 'DESC');
$query->limit(1000);
$query->offset(5);

$first = $query->first();
$res = $query->fetch();
$sql = $query->build();
```

Tips:
- The `where` method support "=" and "<>" comparison with `NULL` (converted to `IS` and `IS NOT`)

### Select query return format

Select queries are specials, they explore your models relations to select every possible fields

Let's say you have a `User` model, which points to the `Person` model through `fk_person`, which points to `PersonPhone` through `fk_phone`, our response format will be

```json
[
    {
        "data":
        {
            "id": "...",
            "login": "...",
            "password": "...",
            "...": "..."
        },
        "fk_person":
        {
            "data":
            {
                "firstname": "bob",
                "lastname": "robertson",
                "...": "..."
            },
            "fk_phone":
            {
                "number": "0123456789",
                "...": "..."
            }
        }
    }
]
```

It can seem quite hard to use at first, but it is really simple:
- use a foreign key name to access a foreign table
- use `data.[key-name]` on specified table to access data

Example: to access our user's phone number, we can access

`user.fk_person.fk_phone.data.number`

### Bottleneck model exploration

Using those prototypes
```php
DatabaseQuery::exploreModel(string $model, bool $recursive=true, array $foreignKeyIgnores=[]): self;
Model::select(bool $recursive=true, array $foreignKeyIgnores=[]);
```

We can control how `DatabaseQuery` explore our "model tree"

By setting `$recursive` to `false`, we only fetch our model data, and don't explore more

Putting relations in `$foreignKeysIgnores` as `table&foreign_key[&foreign_key]` :
- To ignore the phone key, we can put `"user&fk_person&fk_phone"`
- Putting `"user&fk_person"` will also ignore every model that depends on it (`"user&fk_person&fk_phone"` in this case)


## UPDATE query

```php
$query = new DatabaseQuery("user", DatabaseQuery::UPDATE);

$query->set("created_this_year", true)
$query->set("active", false)

$query->where("my_field", 5) ;
$query->where("my_field", null, "<>");
$query->whereSQL("creation_date > {}", ['2023-01-01']);
$query->order('user', 'creation_date', 'DESC');
$query->limit(1000);

$res = $query->fetch();
$sql = $query->build();
```

## DELETE query

```php
$query = new DatabaseQuery("user", DatabaseQuery::DELETE);

$query->where("my_field", 5) ;
$query->where("my_field", null, "<>");
$query->whereSQL("creation_date > {}", ['2023-01-01']);
$query->order('user', 'creation_date', 'DESC');
$query->limit(1000);

$first = $query->first();
$res = $query->fetch();
$sql = $query->build();
```

## Configuration

`DatabaseQuery` don't have a big configurable, so far, you can only change the maximum number of `JOIN` a query can handle (50 by default)

```json
"database-query": {
    "join-limit": 50
}
```

[< Back to summary](../README.md)
