<?php


namespace Sharp\Classes\Core;

use JsonException;
use Sharp\Classes\Core\Component;
use Sharp\Classes\Env\Storage;
use Throwable;

class Logger
{
    use Component;

    protected $stream = null;
    protected string $filename;

    public static function getDefaultInstance()
    {
        return new self("sharp.csv");
    }

    public function __construct(string $filename, Storage $storage=null)
    {
        $storage ??= Storage::getInstance();

        $this->filename = $storage->path($filename);
        $exists = $storage->isFile($filename);

        $this->stream = $storage->getStream($filename, "a", false);
        if (!$exists)
            fputcsv($this->stream, [
                "DateTime",
                "IP",
                "Method",
                "Level",
                "Message"
            ], "\t");
    }

    public function __destruct()
    {
        if ($this->stream)
            fclose($this->stream);
    }

    public function getPath(): string
    {
        return $this->filename;
    }

    protected function toString(mixed $content)
    {
        if (is_string($content) || is_numeric($content))
            return "$content";

        try
        {
            return json_encode($content, JSON_THROW_ON_ERROR);
        }
        catch (JsonException $err)
        {
            return print_r($content, true);
        }
    }

    public function log(string $level, mixed ...$content)
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'null';
        $method = $_SERVER['REQUEST_METHOD'] ?? php_sapi_name();
        $now = date('Y-m-d H:i:s');

        foreach ($content as $line)
        {
            $line = $this->toString($line);
            if (is_resource($this->stream))
            {
                fputcsv($this->stream, [
                    $now,
                    $ip,
                    $method,
                    $level,
                    $line
                ], "\t");
            }
            else
            {
                echo "Error while shutting down : $line \n";
            }

        }
    }

    public function debug       (mixed ...$messages) { $this->log("debug", ...$messages); }
    public function info        (mixed ...$messages) { $this->log("info", ...$messages); }
    public function notice      (mixed ...$messages) { $this->log("notice", ...$messages); }
    public function warning     (mixed ...$messages) { $this->log("warning", ...$messages); }
    public function error       (mixed ...$messages) { $this->log("error", ...$messages); }
    public function critical    (mixed ...$messages) { $this->log("critical", ...$messages); }
    public function alert       (mixed ...$messages) { $this->log("alert", ...$messages); }
    public function emergency   (mixed ...$messages) { $this->log("emergency", ...$messages); }

    public function logThrowable(Throwable $throwable) {
        $this->error("Got an Exception/Error: ". $throwable->getMessage());
        $this->error(sprintf("#- %s(%s)", $throwable->getFile(), $throwable->getLine()));
        $this->error(...explode("\n", $throwable->getTraceAsString()));
    }
}