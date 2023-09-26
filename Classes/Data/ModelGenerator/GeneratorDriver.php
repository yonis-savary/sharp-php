<?php

namespace Sharp\Classes\Data\ModelGenerator;

use Sharp\Classes\Data\Database;
use Sharp\Classes\Data\ObjectArray;

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
        return ObjectArray::fromExplode("_", $name)
        ->filter()
        ->map("ucfirst")
        ->join();
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