<?php

namespace Sharp\Classes\Data\ModelGenerator;

use Sharp\Classes\Data\Database;

abstract class GeneratorDriver
{
    protected Database $connection;

    public function __construct(Database $connection)
    {
        $this->connection = $connection;
    }

    public function sqlNameToPHPName(string $name): string
    {
        $parts = explode("_", $name);
        $parts = array_filter($parts);
        $parts = array_map("ucfirst", $parts);
        return join("", $parts);
    }

    /**
     * @var array<string> Return an array with tables names
     */
    public function listTables(): array
    {
        return [];
    }

    public function generateAll(string $targetApplication): void
    {
        foreach ($this->listTables() as $table)
            $this->generate($table, $targetApplication);
    }

    public abstract function generate(string $table, string $targetApplication);
}