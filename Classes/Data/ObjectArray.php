<?php

namespace Sharp\Classes\Data;

class ObjectArray
{
    /**
     * @param array $data Initial data for the array
     */
    public function __construct(protected array $data=[])
    {}


    /**
     * Alias to the constructor
     */
    public static function fromArray(array $data=[]): self
    {
        return new self($data);
    }

    /**
     * Create an array from an `explode()` method
     *
     * @param string $separator `explode()` separator parameter
     * @param string $string `explode()` $string parameter
     * @param int $limit `explode()` $limit parameter
     */
    public static function fromExplode(string $separator, string $string, int $limit=PHP_INT_MAX)
    {
        $data = explode($separator, $string, $limit);
        return new self($data);
    }

    /**
     * Append values to the data
     * @note This method edit the array in place
     */
    public function push(mixed ...$objects): self
    {
        array_push($this->data, ...$objects);
        return $this;
    }

    /**
     * Remove the last value of the array
     * @note This method edit the array in place
     */
    public function pop(): self
    {
        array_pop($this->data);
        return $this;
    }

    /**
     * Remove the first element of the array
     * @note This method edit the array in place
     */
    public function shift(): self
    {
        array_shift($this->data);
        return $this;
    }

    /**
     * Prepend new values to the array
     * @note This method edit the array in place
     */
    public function unshift(mixed ...$objects): self
    {
        array_unshift($this->data, ...$objects);
        return $this;
    }

    /**
     * Execute a function for every array's items
     *
     * @param callable $callback Callback to execute
     * @note Return the ObjectArray object
     */
    public function forEach(callable $callback): self
    {
        array_walk($this->data, $callback);
        return $this;
    }

    /**
     * `array_map` equivalent for ObjectArray instance
     *
     * @param callable $callback Callback to execute
     * @note Return a NEW ObjectArray object with edited data
     */
    public function map(callable $callback): self
    {
        return new self(array_map($callback, $this->data));
    }

    /**
     * `array_filter` equivalent for ObjectArray instance
     *
     * @param callable $callback Callback to execute
     * @note Return a NEW ObjectArray object with edited data
     */
    public function filter(callable $callback=null): self
    {
        return new self(array_values(array_filter($this->data, $callback)));
    }

    /**
     * `array_unique` equivalent for ObjectArray instance
     *
     * @param callable $callback Callback to execute
     * @note Return a NEW ObjectArray object with edited data
     */
    public function unique(): self
    {
        return new self(array_unique($this->data));
    }

    /**
     * Remove values from the instance's data
     *
     * @note Return a NEW ObjectArray object with edited data
     */
    public function diff(array $valuesToRemove): self
    {
        return new self(array_diff($this->data, $valuesToRemove));
    }

    /**
     * Slice the values from the instance's data
     *
     * @note Return a NEW ObjectArray object with edited data
     */
    public function slice(int $offset, int $size=null): self
    {
        return new self(array_slice($this->data, $offset, $size));
    }

    /**
     * Return the instance's data
     */
    public function collect(): array
    {
        return $this->data;
    }

    /**
     * @return string Imploded values
     */
    public function join(string $glue=""): string
    {
        return join($glue, $this->data);
    }

    /**
     * @return int Size of contained data
     */
    public function length(): int
    {
        return count($this->data);
    }

    /**
     * Return the first element that respect a callback
     *
     * @param callable $filter Filter is a callback, each element is given to it, must return a boolean
     * @return mixed|null Return found object or null if not found
     */
    public function find(callable $filter): mixed
    {
        foreach ($this->data as $element)
        {
            if ($filter($element) === true)
                return $element;
        }
        return null;
    }

    /**
     * Combine returned values into a new associative array
     *
     * @param callable $entriesMaker This callback must return an array of two, the first element is the key, the second is the value
     * @return array Associative array made of returned pair values
     */
    public function combine(callable $entriesMaker): array
    {
        $newData = [];
        foreach ($this->data as $row)
        {
            list($key, $value) = $entriesMaker($row);
            $newData[$key] = $value;
        }
        return $newData;
    }
}