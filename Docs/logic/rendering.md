[< Back to Summary](../README.md)

# 🖌️ View Rendering

PHP is a template engine, we don't need to add a template engine on top of another one

Rendering can be made through simple PHP files with `<?php` and `<?=`

## Making a view

In this example we will make a simple view that display a number

`MyApp/Views/display-number.php`
```php
The number is <?= $number ?>.
```

To render it, we simply use the `Renderer` component

```php
// Get the content as a string
$content = Renderer::getInstance()->render("display-number", ["number" => 28064212]);

// Get a `Response` object
$response = Response::render("display-number", ["number" => 28064212]);
```

## Using Renderer helpers

The `Renderer` don't resume itself to requiring a view file, it also
got some additional features that can be useful (or even essential)

### Rendering a sub-template

You can render a template inside another one with the `render()` function

```php
Articles
<?= render("article-list") ?>
```

### Using a Template as a Base

While making your application, there is a good chance that your
views will have the same base, `Renderer` also support this kind of setup

First, we make our parent view, which can use variables and `section()` helper

`MyApp/Views/common.php`:
```text
<html>
    <head>
        <title>
            <?php $title ?? "MyApp" ?>
        </title>
    </head>
    <body>
        <!-- Yield content from child view -->
        <?= section("content") ?>
    </body>
</html>
```

Then we can use it by using `template()` and `start()/stop()` :
- `template()` tells `Renderer` that we want to use a parent view
- `start($sectionName)/stop()` tells `Renderer` that we are filling a section content that can be retrieved with `section()`

```php
<?= template("common", ["title" => "Article list"]) ?>

<?= start("content") ?>
    Articles
    <?= render("articles/list") ?>
```

## Rendering inside your application

With Sharp, they are two ways of rendering inside your application

```php
# First method, with any Renderer object
$renderer = new Renderer;
$html = $renderer->render("myView");
$html = $renderer->render("myView", ["title" => "Hello!"]);

# Second method, directly through Response class to generate a response
$response = Response::view("myView", ["title" => "Hello!"]);
```

[< Back to Summary](../README.md)