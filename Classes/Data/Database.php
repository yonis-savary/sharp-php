<?php

namespace Sharp\Classes\Data;

use PDO;
use PDOException;
use PDOStatement;
use Sharp\Classes\Core\Component;
use Sharp\Classes\Core\Configurable;
use Sharp\Classes\Env\Storage;

class Database
{
    use Component, Configurable;

    protected ?PDO $connection = null;

    protected PDOStatement $lastStatement;

    public static function getDefaultConfiguration(): array
    {
        return [
            "driver" => "mysql",
            "database" => "database",
            "host" => "localhost",
            "port" => 3306,
            "user" => "root",
            "password" => null,
            "enable-foreign-keys" => true
        ];
    }

    public static function getDefaultInstance()
    {
        $configuration = self::readConfiguration();
        return new self(
            $configuration["driver"],
            $configuration["database"],
            $configuration["host"],
            $configuration["port"],
            $configuration["user"],
            $configuration["password"]
        );
    }

    public function __construct(
        public string $driver,
        public ?string $database,
        public ?string $host,
        public ?int $port,
        public ?string $user,
        protected ?string $password
    )
    {
        $dsn = $this->getDSN();
        $config = $this->getConfiguration();
        $this->connection = new PDO($dsn, $user, $password);

        if ($driver === "sqlite" && $config["enable-foreign-keys"])
            $this->query("PRAGMA foreign_keys=ON");
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function isConnected(): bool
    {
        return $this->connection !== null;
    }

    public function getDSN(): string
    {
        $driver = $this->driver;
        $dbname = $this->database;
        $host = $this->host;
        $port = $this->port;

        if ($driver == 'sqlite')
        {
            if (!$dbname)
                return 'sqlite::memory:';

            $path = Storage::getInstance()->path($dbname);
            return "sqlite:$path";
        }

        return "{$driver}:host={$host};port={$port};dbname={$dbname}";
    }

    /**
     * Shortcut to `(PDO)->quote()`
     */
    public function quote($value): string
    {
        return $this->connection->quote($value);
    }

    /**
     * @return int The last inserted Id by the connection (if any, `false` otherwise)
     */
    public function lastInsertId(): int|false
    {
        return $this->connection->lastInsertId();
    }

    protected function prepareString($str, $quote=false): string
    {
        if (is_array($str))
        {
            $template = "(". join(",", array_map(fn()=>"{}", $str)) .")";
            return $this->build($template, $str);
        }

        if ($str === null)
            return 'NULL';

        if ($str === true)
            $str = 1;
        if ($str === false)
            $str = 0;

        $str = preg_replace('/([\'\\\\])/', '$1$1', $str);

        return ($quote) ?
            "'$str'":
            $str;
    }

    /**
     * Build a query by replacing placeholders (`{}`) with `$context` values
     *
     * @param string $sql Query to complete
     * @param array $context Placeholders-replacing values
     * @example NULL `build('UPDATE ... SET name = {}', ['Dale']) // UPDATE ... SET name = 'Dale'`
     */
    function build(string $sql, array $context=[]): string
    {
        $queryClone = $sql;

        $matchesQuoted = [];
        // Un-escaped regex : (['"`])(?:.+?(?:\1\1|\\\1)?)+?\1
        preg_match_all('/([\'"`])(?:.*?(?:\\1\\1|\\\\\\1)?)+?\\1/', $sql, $matchesQuoted, PREG_OFFSET_CAPTURE);

        $quotedPositions = [];
        foreach ($matchesQuoted[0] as $m)
        {
            $offset = 0;
            while (($pos = strpos($m[0], '{}', $offset)) !== false)
            {
                $quotedPositions[] = $m[1] + $pos;
                $offset = $pos+1;
            }
        }

        $count = 0;
        $queryClone = preg_replace_callback('/\{\}/',
        function($match) use (&$count, $quotedPositions, $context) {
            $doQuote = !in_array($match[0][1], $quotedPositions);
            $val = $this->prepareString($context[$count] ?? null, $doQuote);
            $count++;
            return $val;
        }, $queryClone, flags:PREG_OFFSET_CAPTURE);

        return $queryClone;
    }

    public function query(
        string $query,
        array $context=[],
        int $fetchMode=PDO::FETCH_ASSOC,
        bool $returnStr=false
    ): array|string {
        $queryWithContext = $this->build($query, $context);

        if ($returnStr)
            return $queryWithContext;

        $statement = $this->connection->query($queryWithContext);
        $response = $statement->fetchAll($fetchMode);

        $this->lastStatement = $statement;

        return $response;
    }

    public function getLastStatement(): PDOStatement
    {
        return $this->lastStatement;
    }

    public function hasTable(string $table): bool
    {
        try
        {
            $this->query("SELECT 1 FROM `{}` LIMIT 1", [$table]);
            return true;
        }
        catch (PDOException)
        {
            return false;
        }
    }

    public function hasField(string $table, string $field): bool
    {
        try
        {
            $this->query("SELECT `{}` FROM `{}` LIMIT 1", [$field, $table]);
            return true;
        }
        catch (PDOException)
        {
            return false;
        }
    }
}