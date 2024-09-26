<?php

namespace Sharp\Classes\Data;

use InvalidArgumentException;
use OutOfRangeException;
use Sharp\Core\Utils;
use Throwable;

class ObjectArray
{
    protected array $transformers = [];

    /**
     * @param array $data Initial data for the array
     */
    public function __construct(protected array $data=[])
    {}

    public function __clone()
    {
        $instance = new self($this->data);
        $instance->transformers = $this->transformers;
        return $instance;
    }

    /**
     * Alias to the constructor
     */
    public static function fromArray(array $data=[]): self
    {
        return new self($data);
    }

    /**
     * Create an ObjectArray instance with lines from a file as elements
     */
    public static function fromFileLines(string $path, bool $filterEmptyLines=true): self
    {
        $lines = file_get_contents($path);
        $lines = explode("\n", $lines);

        $object = new self($lines);

        if ($filterEmptyLines)
            return $object->filter(fn($line) => trim($line) != "");

        return $object;
    }

    /**
     * Create an ObjectArray instance from an array inside a JSON file
     */
    public static function fromJSONFile(string $path): self
    {
        $content = file_get_contents($path);
        $object = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

        if ((!is_array($object)) || Utils::isAssoc($object))
            throw new InvalidArgumentException("$path file does not contains an array object");

        return new self($object);
    }

    /**
     * Create an array from an `explode()` method
     *
     * @param string $separator `explode()` separator parameter
     * @param string $string `explode()` $string parameter
     * @param int $limit `explode()` $limit parameter
     */
    public static function fromExplode(string $separator, string $string, int $limit=PHP_INT_MAX): self
    {
        $data = explode($separator, $string, $limit);
        return new self($data);
    }


    /**
     * Create an array of values from a SQL query
     * The values will be reduced to the first selected column
     *
     * @example base `fromQuery("SELECT first_name FROM user LIMIT 10") => array of 10 first_name values`
     */
    public static function fromQuery(string $query, array $context=[]): self
    {
        $results = Database::getInstance()->query($query, $context);

        if (! $sample = $results[0] ?? false)
            return new self([]);

        $key = array_keys($sample)[0];
        return (new self($results))->map(fn($x) => $x[$key]);
    }


    /**
     * Write ObjectArray's data in a JSON file
     * @param string $path Target file path
     * @param int $jsonFlags additional flags for json_encode
     * @return self Return self so you can write intermediate data and continue to edit them
     */
    public function writeJSONFile(string $path, int $jsonFlags=JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE): self
    {
        file_put_contents(
            $path,
            json_encode($this->collect(), JSON_THROW_ON_ERROR | $jsonFlags)
        );
        return $this;
    }


    /**
     * Write ObjectArray's data in a text file
     * @param string $path Target file path
     * @param string $glue Glue between data (new line by default)
     * @return self Return self so you can write intermediate data and continue to edit them
     */
    public function writeTextFile(string $path, string $glue="\n"): self
    {
        file_put_contents($path, $this->join($glue));
        return $this;
    }

    /**
     * Return a copy of the ObjectArray instance with a new transformer callback
     * @param callable $callback Callback capable of transforming an array
     * @param bool $takeResultAsData Do we take the callback results as new values ? (Ex: should be false for by-reference callbacks)
     */
    protected function withTransformers(callable $callback=null, bool $takeResultAsData=false): self
    {
        $clone = clone $this;
        $clone->transformers[] = [$callback, $takeResultAsData];
        return $clone;
    }

    /**
     * Append values to the data
     */
    public function push(mixed ...$objects): self
    {
        $data = $this->collect();
        array_push($data, ...$objects);
        $this->data = $data;

        return $this;
    }

    /**
     * Remove the last value of the array
     */
    public function pop(): self
    {
        $data = $this->collect();
        array_pop($data);
        $this->data = $data;

        return $this;
    }

    /**
     * Remove the first element of the array
     */
    public function shift(): self
    {
        return $this->withTransformers(array_shift(...));
    }

    /**
     * Prepend new values to the array
     */
    public function unshift(mixed ...$objects): self
    {
        return $this->withTransformers(fn(&$arr) => array_unshift($arr, ...$objects));
    }

    /**
     * Execute a function for every array's items
     *
     * @param callable $callback Callback to execute
     * @param mixed $arg [optional] If the optional userdata parameter is supplied, it will be passed as the third parameter to the callback funcname.
     */
    public function forEach(callable $callback, mixed $arg=null): self
    {
        $data = $this->collect();
        array_walk($data, $callback, $arg);
        // Overwrite data if $callback change values by reference
        $this->data = $data;
        return $this;
    }

    /**
     * `array_map` equivalent for ObjectArray instance
     *
     * @param callable $callback Callback to execute
     */
    public function map(callable $callback): self
    {
        return $this->withTransformers(fn($arr) => array_map($callback, $arr), true);
    }

    /**
     * `array_map(intval(...))` equivalent for ObjectArray instance
     * Map every values to integers
     */
    public function asIntegers(bool $filterNullValues=true): self
    {
        $newObject = $this->map(fn($x) => is_numeric($x) ? intval($x) : null);
        if ($filterNullValues)
            return $newObject->filter(fn($x) => $x !== null);

        return $newObject;
    }

    /**
     * `array_map(floatval(...))` equivalent for ObjectArray instance
     * Map every values to integers
     */
    public function asFloats(bool $filterNullValues=true): self
    {
        $newObject = $this->map(fn($x) => is_numeric($x) ? floatval($x) : null);
        if ($filterNullValues)
            return $newObject->filter(fn($x) => $x !== null);

        return $newObject;
    }

    /**
     * `array_map(fn($x) => "$x")` equivalent for ObjectArray instance
     * Map every values to integers
     */
    public function asStrings(): self
    {
        return $this->map(fn($x) => "$x");
    }

    /**
     * `array_filter` equivalent for ObjectArray instance
     *
     * @param callable $callback Callback to execute
     */
    public function filter(callable $callback=null): self
    {
        return $this->withTransformers(fn($arr) => array_values(array_filter($arr, $callback)), true);
    }

    /**
     * `array_unique` equivalent for ObjectArray instance
     *
     * @param callable $callback Callback to execute
     */
    public function unique(): self
    {
        return $this->withTransformers(fn($arr) => array_values(array_unique($arr)), true);
    }

    /**
     * Remove values from the instance's data
     */
    public function diff(array $valuesToRemove): self
    {
        return $this->withTransformers(fn(&$arr) => array_values(array_diff($this->data, $valuesToRemove)), true);
    }

    /**
     * Slice the values from the instance's data
     *
     * @note Return a NEW ObjectArray object with edited data
     */
    public function slice(int $offset, int $size=null): self
    {
        return $this->withTransformers(fn($arr) => array_slice($arr, $offset, $size), true);
    }

    /**
     * Reverse elements order (apply array_reverse on data)
     *
     * @note Return a NEW ObjectArray object with edited data
     */
    public function reverse(): self
    {
        return $this->withTransformers(array_reverse(...), true);
    }

    /**
     * Sort elements by a given key itself given by a callback
     *
     * ```php
     * $accounts = $accounts->sortByKey(fn($account) => $account["balance"])
     * ```
     *
     * @note Return a NEW ObjectArray object with edited data
     */
    public function sortByKey(callable $callback, bool $reversed=false): self
    {
        return $this->withTransformers(function($data) use ($callback, $reversed) {
            usort($data, fn($a, $b) => $callback($a) < $callback($b) ? -1 : 1);
            return $reversed ? array_reverse($data): $data;
        }, true);
    }

    /**
     * Return the instance's data
     */
    public function collect(): array
    {
        if (!count($this->transformers))
            return $this->data;

        $data = $this->data;

        foreach ($this->transformers as [$callback, $takeResultAsData])
        {
            if ($takeResultAsData)
                $data = $callback($data);
            else
                $callback($data);
        }

        # Optimization : setting processed data as data and emptying transformers
        # allow us to process those data only one time
        # if we call length() then collect(), the data shall be processed only one time
        $this->data = $data;
        $this->transformers = [];

        return $data;
    }

    /**
     * @return string Imploded values
     */
    public function join(string $glue=""): string
    {
        return join($glue, $this->collect());
    }

    /**
     * @return int Size of contained data
     */
    public function length(): int
    {
        return count($this->collect());
    }

    /**
     * Return the first element that respect a callback
     *
     * @param callable $filter Filter is a callback, each element is given to it, must return a boolean
     * @return mixed|null Return found object or null if not found
     */
    public function find(callable $filter): mixed
    {
        $index = $this->findIndex($filter);
        if ($index === -1)
            return null;

        return $this->getIndex($index);
    }

    /**
     * Return the index of the first element that respect a callback
     *
     * @param callable $filter Filter is a callback, each element is given to it, must return a boolean
     * @return mixed|null Return the index (0+) or -1 if not found
     */
    public function findIndex(callable $filter): int
    {
        $i = 0;
        foreach ($this->collect() as $element)
        {
            if ($filter($element) === true)
                return $i;
            $i++;
        }
        return -1;
    }

    /**
     * Get data element at a specified index
     */
    public function getIndex(int $index): mixed
    {
        $data = $this->collect();
        if ( $index<0 || (count($data) < ($index+1)))
            throw new OutOfRangeException("Index $index does not exists");

        return $data[$index];
    }

    /**
     * Combine returned pairs into a new associative array
     *
     * @param callable $entriesMaker This callback must return an array of two, the first element is the key, the second is the value
     * @return array Associative array made of returned pair values
     */
    public function toAssociative(callable $entriesMaker): array
    {
        $newData = [];

        $data = $this->collect();
        $count = count($data);

        for ($i=0; $i<$count; $i++)
        {
            list($key, $value) = $entriesMaker($data[$i], $i);
            $newData[$key] = $value;
        }
        return $newData;
    }

    /**
     * Combine returned values into a new associative array
     *
     * @param callable $entriesMaker This callback must return an array of two, the first element is the key, the second is the value
     * @return array Associative array made of returned pair values
     * @deprecated renamed, use `toAssociative()` instead
     */
    public function combine(callable $entriesMaker): array
    {
        return $this->toAssociative($entriesMaker);
    }

    public function reduce(callable $callback, mixed $initial=null): mixed
    {
        return array_reduce($this->collect(), $callback, $initial);
    }

    /**
     * @return `true` if any of the array's values respect a given condition
     */
    public function any(callable $condition): bool
    {
        foreach ($this->collect() as $value)
        {
            if ($condition($value) === true)
                return true;
        }
        return false;
    }

    /**
     * @return `true` if ALL of the array's values respect a given condition
     */
    public function all(callable $condition): bool
    {
        foreach ($this->collect() as $value)
        {
            if ($condition($value) !== true)
                return false;
        }
        return true;
    }
}