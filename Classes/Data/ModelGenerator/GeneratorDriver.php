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

    /**
     * snake_case to PascalCase converted
     */
    protected function sqlToPHPName(string $name): string
    {
        $parts = explode("_", $name);
        $parts = array_filter($parts);
        $parts = array_map("ucfirst", $parts);
        return join("", $parts);
    }

    /**
     * @var array<string> Return an array with tables names
     */
    abstract public function listTables(): array;

    public function generateAll(string $targetApplication): void
    {
        foreach ($this->listTables() as $table)
            $this->generate($table, $targetApplication);
    }

    abstract public function generate(string $table, string $targetApplication);
}