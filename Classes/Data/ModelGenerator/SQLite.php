<?php

namespace Sharp\Classes\Data\ModelGenerator;

use Sharp\Classes\CLI\Terminal;
use Sharp\Classes\Data\DatabaseField;
use Sharp\Core\Utils;

class SQLite extends GeneratorDriver
{
    // https://www.sqlite.org/datatype3.html
    const TYPES_REGEX = [
        'INT',
        'INTEGER',
        'TINYINT',
        'SMALLINT',
        'MEDIUMINT',
        'BIGINT',
        'UNSIGNED BIG INT',
        'INT2',
        'INT8',
        'CHARACTER\(\d+\)',
        'VARCHAR\(\d+\)',
        'VARYING CHARACTER\(\d+\)',
        'NCHAR\(\d+\)',
        'NATIVE CHARACTER\(\d+\)',
        'NVARCHAR\(\d+\)',
        'TEXT',
        'CLOB',
        'BLOB',
        'REAL',
        'DOUBLE',
        'DOUBLE PRECISION',
        'FLOAT',
        'NUMERIC',
        'DECIMAL\(\d+ ?, ?\d+\)',
        'BOOLEAN',
        'DATE',
        'DATETIME',
        'TIMESTAMP'
    ];

    protected array $schemaCache = [];
    protected ?string $currentPrimaryKey = null;
    protected array $fieldExtras = [];

    public function listTables(): array
    {
        $res = $this->connection->query("SELECT * FROM sqlite_master");

        $tables = [];
        foreach ($res as &$row)
        {
            if (str_starts_with($row["name"], "sqlite_"))
                continue;

            $table = $row["name"];
            $this->schemaCache[$table] = $row["sql"];
            $tables[] = $table;
        }

        return $tables;
    }

    public function getTableFields(string $createTableScript): array
    {
        $sql = $createTableScript;

        $sql = str_replace("\n", ' ', $sql);
        $sql = preg_replace('/^CREATE TABLE .+? \(/', '', $sql);
        $sql = preg_replace('/\)\s*$/s', '', $sql);

        $parenthesisCount = 0;
        $lines = [];
        $currentLine = '';
        $chars = str_split($sql);
        for ($i=0; $i<count($chars); $i++)
        {
            $char = $chars[$i];

            if ($char == '(')
                $parenthesisCount++;
            else if ($char == ')')
                $parenthesisCount--;

            if ($char === ',' && $parenthesisCount==0)
            {
                $lines[] = $currentLine;
                $currentLine = '';
            }
            else
            {
                $currentLine.=$char;
            }
        }
        $lines[]= $currentLine;
        $lines = array_map('trim', $lines);

        return array_values(array_filter(array_map(fn($l) => $this->lineToField($l), $lines)));
    }

    public function lineToField(string $sqlLine): array
    {
        $field = "";

        $matches = [];
        $typesNames = join('|', self::TYPES_REGEX);
        $columnRegex = "/^(.+?) ($typesNames)/";

        if (preg_match($columnRegex, $sqlLine, $matches))
        {
            $fieldName = $matches[1];
            $field = "'$fieldName' => (new DatabaseField('$fieldName'))";

            $field .= "->hasDefault(".(str_contains($sqlLine, 'DEFAULT') ? "true": "false").")";
            if (str_contains($sqlLine, 'PRIMARY KEY'))
                $this->currentPrimaryKey = $matches[1];

            $matches = [];
            if (preg_match('/REFERENCES (.+?)\((.+?)\)/', $sqlLine, $matches))
                $field .= "->references(".$this->sqlNameToPHPName($matches[1])."::class, '".$matches[2]."')";

            return [$fieldName, $field];
        }

        $matches = [];
        if (preg_match('/^FOREIGN KEY \((.+?)\) REFERENCES (.+?)\((.+?)\)$/', $sqlLine, $matches))
            $this->fieldExtras[$matches[1]] = "->references(".$this->sqlNameToPHPName($matches[2])."::class, '".$matches[3]."')";
    }

    public function generate(string $table, string $targetApplication): void
    {
        $classBasename = $this->sqlNameToPHPName($table);

        $fileName = "$classBasename.php";
        $fileDir = Utils::joinPath($targetApplication, "Models");
        $filePath = Utils::joinPath($fileDir, $fileName);

        if (!is_dir($fileDir)) mkdir($fileDir);
        $classname = Utils::pathToNamespace($fileDir);

        $descriptionRaw = $this->schemaCache[$table];
        $fields = $this->getTableFields($descriptionRaw);

        foreach ($fields as &$field)
        {
            list($name, $string) = $field;
            $string .= $this->fieldExtras[$name] ?? "";
            $field = $string;
        }

        file_put_contents($filePath, Terminal::stringToFile(
        "<?php

        namespace $classname;

        use ".DatabaseField::class.";

        class $classBasename
        {
            use \Sharp\Classes\Data\Model;

            public static function getTable(): string
            {
                return \"$table\";
            }

            public static function getPrimaryKey(): string|null
            {
                return '".$this->currentPrimaryKey."';
            }

            public static function getFields(): array
            {
                return [
                    ".join(",\n\t\t\t", $fields)."
                ];
            }
        }
        ",
        2));
    }
}