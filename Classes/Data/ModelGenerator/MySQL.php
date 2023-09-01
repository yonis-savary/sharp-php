<?php

namespace Sharp\Classes\Data\ModelGenerator;

use Sharp\Classes\CLI\Terminal;
use Sharp\Classes\Data\DatabaseField;
use Sharp\Core\Utils;

class MySQL extends GeneratorDriver
{
    public function listTables(): array
    {
        $db = $this->connection;
        $res = $db->query("SHOW TABLES");

        foreach ($res as &$arr)
            $arr = array_values($arr)[0];

        return $res;
    }

    protected function getFieldDescription(array $fieldDescription, array $foreignKey=null, mixed &$primaryKey): string
    {
        list($field, $type, $null, $key, $default, $extras) = array_values($fieldDescription);
        $string = "'$field' => (new DatabaseField('$field'))";

        $classType = "STRING";
        if (preg_match("/int\(/", $type))           $classType = "INTEGER";
        if (preg_match("/float\(/", $type))         $classType = "FLOAT";
        if (preg_match("/smallint\(1\)/", $type))   $classType = "BOOLEAN";
        if (preg_match("/decimal/", $type))         $classType = "DECIMAL";
        $string .= "->setType(DatabaseField::$classType)";

        $string .= "->setNullable(". ($null=="YES" ? "true": "false") .")";

        if ($ref = $foreignKey[$field] ?? false)
            $string .= "->references(".$this->sqlNameToPHPName($ref[0])."::class, '$ref[1]')";

        if ($key === "PRI")
            $primaryKey ??= $field;

        return "'$field' => $string";
    }

    public function generate(string $table, string $targetApplication): void
    {
        $db = $this->connection;
        $databaseName = $db->database;

        $classBasename = $this->sqlNameToPHPName($table);

        $fileName = "$classBasename.php";
        $fileDir = Utils::joinPath($targetApplication, "Models");
        $filePath = Utils::joinPath($fileDir, $fileName);

        if (!is_dir($fileDir)) mkdir($fileDir);
        $classname = Utils::pathToNamespace($fileDir);


        $foreignKeysRaw = $db->query("
        SELECT COLUMN_NAME as source_field,
               REFERENCED_TABLE_NAME as target_table,
               REFERENCED_COLUMN_NAME as target_field
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = {}
              AND TABLE_NAME = {}
              AND REFERENCED_COLUMN_NAME IS NOT NULL;
        ", [$databaseName, $table]);

        $foreignKeys = [];
        $usedModels = [];
        foreach ($foreignKeysRaw as $row)
        {
            $foreignKeys[$row["source_field"]] = [$row["target_table"], $row["target_field"]];
            $usedModels[] = $row["target_table"];
        }

        $usedModels = array_unique($usedModels);
        $usedModels = array_map(fn($e) => $this->sqlNameToPHPName($e), $usedModels);
        $usedModels = array_map(fn($e) => Utils::joinPath($fileDir, $e), $usedModels);
        $usedModels = array_map(fn($e) => Utils::pathToNamespace($e), $usedModels);
        $usedModels = array_map(fn($e) => "use $e;", $usedModels);

        $primaryKey = null;

        $descriptionRaw = $db->query("DESCRIBE `$table`");
        $description = array_map(function($e) use ($foreignKeys, &$primaryKey) {
            return $this->getFieldDescription($e, $foreignKeys, $primaryKey);
        }, $descriptionRaw);


        file_put_contents($filePath, Terminal::stringToFile(
        "<?php

        namespace $classname;

        use ".DatabaseField::class.";
        ".join("\n", $usedModels)."

        class $classBasename
        {
            use \Sharp\Classes\Data\Model;

            public static function getTable(): string
            {
                return \"$table\";
            }

            public static function getPrimaryKey(): string
            {
                return ". ($primaryKey ? "'$primaryKey'" : 'null') .";
            }

            public static function getFields(): array
            {
                return [
                    ".join(",\n\t\t\t", $description)."
                ];
            }
        }
        ",
        2));
    }
}