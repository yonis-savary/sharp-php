[< Back to Summary](../home.md)

# 🚃 ObjectArray class

Sharp got the [`ObjectArray`](../../Classes/Data/ObjectArray.php) class, which purpose is to be an Object version of an array of data (list, not associative array)

This class got the most of "standart" array methods an array can have, this include
```php
$myArray = new ObjectArray();
$myArray->push();
$myArray->pop();
$myArray->shift();
$myArray->unshift();

$myArray->forEach();
$myArray->map();
$myArray->filter();
$myArray->reduce();

$myArray->unique();
$myArray->diff();
$myArray->slice();
$myArray->reverse();
```

Most of them do exactly what you expect them to do

But this class data handling is quite particular,
when calling

```$myArray->map($myFunction)```

**Callbacks are not applied, until you call `$myArray->collect()`, which return the new data**

Every functions above return a new `ObjectArray` instance with a new filter/transformer, which means that you can create copies

```php
$original = new ObjectArray([0,1,2,3,4,5]);
$even = $original->filter(fn($x) => $x % 2 == 0);

$original->collect();
// [0,1,2,3,4,5]

$even->collect();
// [0,2,4]
```

Also, as `ObjectArray` return new instances of itself, this mean that you can chain method calls

```php
(new ObjectArray[0,1,1,2,3,3,4,4,5,6])
->unique()
->filter(fn($x) => $x % 2 == 0)
->map(fn($x) => $x * 2)
->collect()
// [0,4,8,12]
```

## Additionnal properties/methods

```php
// Alias to the constructor, can be used as a callback
ObjectArray::fromArray($myArray);

// Alias to ObjectArray::fromArray(explode($separator, $string))
ObjectArray::fromExplode(",", "A,B,C");

$myArray = new ObjectArray([0,1,2,3,4,5]);

$myArray->join(","); // "0,1,2,3,4,5"
$myArray->length(); // 6

// Return the first element that respect a condition
$myArray->find(fn($x) => $x % 2 == 0) // return 0
$myArray->find(fn($x) => $x >= 4) // return 4

// Check if any element respect a condition
$myArray->any(fn($x) => $x == 5) // true

// Check if every elements respect a condition
$myArray->all(is_numeric(...)); // true

$myArray->reduce(fn($acc, $cur) => $acc + $cur, 0);
// return 15

// Make an associative array from returned pairs
$alphabet = range('A', 'Z');
$myArray->combine(fn($value) => [$alphabet[$i], $i]);
// return ['A'=>0, 'B'=>1, 'C'=>2, 'D'=>3, 'E'=>4, 'F'=>5]
```

[< Back to Summary](../home.md)
