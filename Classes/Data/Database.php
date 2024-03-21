<?php

namespace Sharp\Classes\Data;

use PDO;
use PDOException;
use PDOStatement;
use Sharp\Classes\Core\Component;
use Sharp\Classes\Core\Configurable;
use Sharp\Classes\Core\EventListener;
use Sharp\Classes\Env\Storage;
use Sharp\Classes\Events\ConnectedDatabase;

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
            "charset" => "utf8"
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
            $configuration["password"],
            $configuration["charset"]
        );
    }

    public function __construct(
        public string $driver,
        public ?string $database,
        public ?string $host=null,
        public ?int $port=null,
        public ?string $user=null,
        protected ?string $password=null,
        public string $charset="utf8"
    )
    {
        $this->loadConfiguration();

        $dsn = $this->getDSN();
        $this->connection = new PDO($dsn, $user, $password);

        // Ensure the configuration charset is used
        $charset = $this->charset;
        switch ($this->driver)
        {
            case "sqlite":
                $this->query("PRAGMA encoding={}", [$charset]);
                $this->query("PRAGMA foreign_keys=ON"); // Manually enable foreign keys constraints
                break;
            default :
                $this->query("SET NAMES $charset");
                break;
        }

        EventListener::getInstance()->dispatch(
            new ConnectedDatabase(
                $this->connection,
                $driver,
                $database,
                $host,
                $port,
                $user,
            )
        );
    }

    /**
     * @return ?PDO The current connection to the database (`null` if not connected)
     */
    public function getConnection(): ?PDO
    {
        return $this->connection;
    }

    public function getLastStatement(): PDOStatement
    {
        return $this->lastStatement;
    }

    public function isConnected(): bool
    {
        return $this->connection !== null;
    }

    protected function getDSN(): string
    {
        $driver = $this->driver;
        $dbname = $this->database;
        $host = $this->host;
        $port = $this->port;

        if ($driver === 'sqlite')
        {
            if (!$dbname)
                return 'sqlite::memory:';

            $path = Storage::getInstance()->path($dbname);
            return "sqlite:$path";
        }

        return "{$driver}:host={$host};port={$port};dbname={$dbname}";
    }

    /**
     * @return int The last inserted Id by the connection (if any, `false` otherwise)
     */
    public function lastInsertId(): int|false
    {
        return $this->connection->lastInsertId();
    }

    protected function prepareString(mixed $value, $quote=false): string
    {
        if (is_array($value))
            return $this->build(
                "(". join(",", array_map(fn()=>"{}", $value)) .")",
                $value
            );

        if ($value === null)
            return 'NULL';

        if ($value === true)
            return 'TRUE';

        if ($value === false)
            return 'FALSE';

        $value = preg_replace('/([\'\\\\])/', '$1$1', $value);

        return $quote ? "'$value'": $value;
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

        // This regex capture quoted content
        preg_match_all('/([\'"`])(?:.*?(?:\1\1|\\\1)?)+?\1/', $sql, $matchesQuoted, PREG_OFFSET_CAPTURE);

        $quotedPositions = [];
        foreach ($matchesQuoted[0] as $m)
        {
            $offset = 0;
            while (($pos = strpos($m[0], '{}', $offset)) !== false)
            {
                $quotedPositions[] = $m[1] + $pos;
                $offset = $pos + 1;
            }
        }

        $count = 0;
        $queryClone = preg_replace_callback(
            '/\{\}/',
            function($match) use (&$count, $quotedPositions, $context) {
                $doQuote = !in_array($match[0][1], $quotedPositions);
                $val = $this->prepareString($context[$count] ?? null, $doQuote);
                $count++;
                return $val;
            },
            $queryClone,
            flags:PREG_OFFSET_CAPTURE
        );

        return $queryClone;
    }

    /**
     * Perform a query with the database
     * @param string $query SQL Query to execute
     * @param array $context Data for the query (values replaces placeholders `{}`)
     * @param int $fetchMode PDO Fetch mode constant
     */
    public function query(string $query, array $context=[], int $fetchMode=PDO::FETCH_ASSOC): array
    {
        $queryWithContext = $this->build($query, $context);

        $statement = $this->connection->query($queryWithContext);
        $this->lastStatement = $statement;

        $results = $statement->fetchAll($fetchMode);
        $statement->closeCursor();

        return $results;
    }

    /**
     * @return `true` if the given table exists in the database, `false` otherwise
     */
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

    /**
     * @return `true` if both the given table AND field exists in the database, `false` otherwise
     */
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