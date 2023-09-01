<?php


namespace Sharp\Classes\Core;

use InvalidArgumentException;
use JsonException;
use Sharp\Classes\Core\Component;
use Sharp\Classes\Env\Storage;
use Throwable;

class Logger
{
    use Component;

    protected $stream = null;
    protected bool $closeStream = true;
    protected string $filename;

    public static function getDefaultInstance()
    {
        return new self("sharp.csv");
    }

    public static function fromStream(mixed $stream, bool $autoclose=false): self
    {
        if (!is_resource($stream))
            throw new InvalidArgumentException("\$stream parameter must be a stream");

        $logger = new self();
        $logger->replaceStream($stream, $autoclose);
        return $logger;
    }

    public function __construct(string $filename=null, Storage $storage=null)
    {
        if (!$filename)
            return;

        $storage ??= Storage::getInstance();
        $storage->assertIsWritable();

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
        $this->closeStream();
    }

    public function closeStream(): void
    {
        if ($this->closeStream && $this->stream)
            fclose($this->stream);
    }

    public function replaceStream(mixed $stream, bool $autoclose=false): void
    {
        $this->closeStream();

        if (!is_resource($stream))
            throw new InvalidArgumentException("[\$stream] parameter must be a resource");

        $this->stream = $stream;
        $this->closeStream = $autoclose;
    }

    public function getPath(): string
    {
        return $this->filename;
    }

    protected function toString(mixed $content): string
    {
        if (is_string($content) || is_numeric($content))
            return "$content";

        try
        {
            return json_encode($content, JSON_THROW_ON_ERROR);
        }
        catch (JsonException)
        {
            return print_r($content, true);
        }
    }

    public function log(string $level, mixed ...$content): void
    {
        if (!$this->stream)
            return;

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'null';
        $method = $_SERVER['REQUEST_METHOD'] ?? php_sapi_name();
        $now = date('Y-m-d H:i:s');

        foreach ($content as $line)
        {
            $line = $this->toString($line);
            $line = [$now, $ip, $method, $level, $line];

            if ($this->stream)
                fputcsv($this->stream, $line, "\t");
            else
                echo "Error while shutting down : $line \n";
        }
    }

    public function debug       (mixed ...$messages): void { $this->log("debug", ...$messages); }
    public function info        (mixed ...$messages): void { $this->log("info", ...$messages); }
    public function notice      (mixed ...$messages): void { $this->log("notice", ...$messages); }
    public function warning     (mixed ...$messages): void { $this->log("warning", ...$messages); }
    public function error       (mixed ...$messages): void { $this->log("error", ...$messages); }
    public function critical    (mixed ...$messages): void { $this->log("critical", ...$messages); }
    public function alert       (mixed ...$messages): void { $this->log("alert", ...$messages); }
    public function emergency   (mixed ...$messages): void { $this->log("emergency", ...$messages); }

    public function logThrowable(Throwable $throwable): void
    {
        $this->error("Got an Exception/Error: ". $throwable->getMessage());
        $this->error(sprintf("#- %s(%s)", $throwable->getFile(), $throwable->getLine()));
        $this->error(...explode("\n", $throwable->getTraceAsString()));
    }
}