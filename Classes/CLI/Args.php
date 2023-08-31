<?php

namespace Sharp\Classes\CLI;

/**
 * Command line interface arguments implementation (parsing)
 */
class Args implements \Countable
{
    protected $arguments = [];
    protected $shortOptions = [];

    public function __construct(string $argv=null)
    {
        if (!$argv)
            return;

        $matches = [];
        preg_match_all('/(-+[^ =]+)(?:=("|\')(.+?)\\2|=([^ ]+))?|[^ ]+/', $argv, $matches);

        foreach ($matches as &$x)
        {
            foreach ($x as &$y)
                $y = $y == "" ? null: $y;
        }

        for ($i=0; $i<count($matches[0]); $i++)
        {
            $name = trim($matches[1][$i] ?? $matches[0][$i]);
            $value = null;

            if (preg_match("/^\-[^\-]+$/", $name))
                array_push($this->shortOptions, ...str_split(substr($name, 1)));

            if (str_starts_with($name, "-"))
                $value = trim($matches[4][$i] ?? $matches[3][$i] ?? "");
            else
                $value = $name;

            $value = preg_replace('/^=?("|\')?|("|\')?$/', "", $value ?? "");
            if (!$value)
                $value = null;

            $this->arguments[$name] = $value;
        }
    }

    public static function fromArray($argv): self
    {
        return new self(join(" ", $argv));
    }

    public function __debugInfo()
    {
        return $this->dump();
    }

    public function dump(): array
    {
        return $this->arguments;
    }

    public function toString(): string
    {
        $params = $this->arguments;
        foreach ($params as $key => &$value)
        {
            if (!str_starts_with($key, "-"))
                $value = $value;
            else if ($value)
                $value = $key."=\"".str_replace('"', '\\"', $value).'"';
            else
                $value = $key;
        }

        return join(" ", $params);
    }

    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @return int The number of parameters (with or without value)
     */
    public function count(): int
    {
        return count(array_keys($this->arguments));
    }

    public function list(): array
    {
        return array_values($this->arguments);
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function setArgv(mixed $value): void
    {
        $this->arguments = $value;
    }

    /**
     * @return mixed The value of the parameter, `null` if the parameter is present but has no value, `false` is the parameter is not present
     */
    public function getOption(string $short, string $long): mixed
    {
        $arguments = &$this->arguments;

        if (!str_starts_with($short, "-"))
            $shortName = "-$short";
        if (!str_starts_with($long, "--"))
            $longName = "--$long";

        if (array_key_exists($longName, $arguments))
            return $arguments[$longName];
        if (array_key_exists($shortName, $arguments))
            return $arguments[$shortName];

        if (in_array($short, $this->shortOptions))
            return null;

        return false;
    }

    public function isPresent(string $short, string $long): bool
    {
        return $this->getOption($short, $long) !== false;
    }

    /**
     * Try to retrieve the parameter value, return the value or null otherwise
     */
    public function get(string $short, string $long): mixed
    {
        $value = $this->getOption($short, $long);
        return $value === false ? null: $value;
    }
}