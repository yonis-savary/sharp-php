# LazySearch

This plugin can help you build content tables with :
- pagination
- filters
- search
- sort

## Base Usage

```php
// The ANY method is important here
Router::any("/contact", function(){
    return LazySearch::makeList(
        "SELECT
            id,
            name,
            phone_number,
            city
        FROM contact
    ");
});
```

Note: for LazySearch to work, explicits columns names must be selected, you cannot select `*`

When accessing `/contact`, you should get a `<table>` with the `contact` table content !

Now, let's say that you want to have a link on each contact name leading to its page,
then you have to give it some options


```php
// The ANY method is important here
Router::any("/contact", function(){
    return LazySearch::makeList(
        "SELECT
            id,
            name,
            phone_number,
            city
        FROM contact
    ");
}, LazySearch::makeOptions(
        [LazySearch::makeLink("name", "/contact/", "id")]
));
```

This line
```php
LazySearch::makeLink("name", "/contact/", "id")
```

Means that on every `name` cell, the content will be a link leading to `/contact/{id}` with `{id}` replaced
by its value

## Configuration

| Key                  | Default  | Purpose                                                                                        |
|----------------------|----------|------------------------------------------------------------------------------------------------|
| `locale`             | `en`     | Change lazySearch table UI language (Availables: `en`, `fr`)                                   |
| `ignore_links`       | `true`   | If `true`, add links values columns to ignores                                                 |
| `template`           | `null`   | If non-null render this template instead of the default one, the LazyTable is in `$lazySearch` |
| `size_limit`         | `100`    | Max page size for the client                                                                   |
| `export_middlewares` | `[]`     | Middleware classes for exports (maybe only admins can export for example ?)                    |
| `export_chunk_size`  | `20_000` | Chunk size for exports, a bigger number means faster stream, be aware of PHP memory limit      |
| `use_cache`          | `false`  | Is the cached used for queries informations ?                                                  |
| `cache_time_to_live` | `60*5`   | By default, a query data is in cache for 5 minutes                                             |