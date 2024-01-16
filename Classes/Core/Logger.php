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

    /**
     * Create a logger from a stream (which must be writable)
     *
     * @param resource $stream Output stream to write to
     * @param bool $autoClose If `true`, the Logger will close the stream on destruct
     */
    public static function fromStream(mixed $stream, bool $autoClose=false): self
    {
        $logger = new self();
        $logger->replaceStream($stream, $autoClose);

        return $logger;
    }

    /**
     * @param ?string $filename File BASENAME for the Logger
     * @param ?Storage $storage Optional target Storage directory (global instance if `null`)
     * @example NULL `new Logger('error.csv', new Storage('/var/log/sharp/my-app'))`
     */
    public function __construct(string $filename=null, Storage $storage=null)
    {
        if (!$filename)
            return;

        $storage ??= Storage::getInstance();
        $exists = $storage->isFile($filename);

        if (!$exists)
            $storage->assertIsWritable();

        $this->filename = $storage->path($filename);
        $this->stream = $storage->getStream($filename, "a", false);

        if (!$exists)
            fputcsv($this->stream, ["DateTime", "IP", "Method", "Level", "Message"], "\t");
    }

    public function __destruct()
    {
        $this->closeStream();
    }

    protected function closeStream(): void
    {
        if ($this->closeStream && $this->stream)
            fclose($this->stream);
    }

    /**
     * Replace the Logger stream with another
     *
     * @param resource $stream Output stream that replace the current one
     * @param bool $autoClose If `true`, the Logger will close the stream on destruct
     */
    public function replaceStream(mixed $stream, bool $autoClose=false): void
    {
        if (!is_resource($stream))
            throw new InvalidArgumentException('$stream parameter must be a resource');

        $this->closeStream();
        $this->stream = $stream;
        $this->closeStream = $autoClose;
    }

    /**
     * @return string Absolute path to the Logger's output file
     */
    public function getPath(): string
    {
        return $this->filename;
    }

    /**
     * @return string `$content` represented as a string
     */
    protected function toString(mixed $content): string
    {
        if (is_string($content) || is_numeric($content))
            return strval($content);

        try
        {
            return json_encode($content, JSON_THROW_ON_ERROR);
        }
        catch (JsonException)
        {
            return print_r($content, true);
        }
    }

    /**
     * Directly log a line(s) into the output stream
     *
     * @param string $level Log level, can be a custom one
     * @param mixed ...$content Information/Objects to log (can be of any type)
     */
    public function log(string $level, mixed ...$content): void
    {
        if (!$this->stream)
            return;

        $ip = $_SERVER['REMOTE_ADDR'] ?? "0.0.0.0";
        $method = $_SERVER['REQUEST_METHOD'] ?? php_sapi_name();
        $now = date('Y-m-d H:i:s');

        foreach ($content as $line)
        {
            if ($line instanceof Throwable)
            {
                $this->logThrowable($level, $line);
                continue;
            }

            $lineString = $this->toString($line);
            fputcsv($this->stream, [$now, $ip, $method, $level, $lineString], "\t");
        }
    }

    /**
     * Log a throwable message into the output plus its trace
     * (Useful to debug a trace and/or errors)
     */
    protected function logThrowable(string $level, Throwable $throwable): void
    {
        $this->log($level,
            "Got an [". $throwable::class ."] Throwable: ". $throwable->getMessage(),
            sprintf("#- %s(%s)", $throwable->getFile(), $throwable->getLine()),
            ...explode("\n", $throwable->getTraceAsString())
        );
    }

    /**
     * Log a "debug" level line
     * @param mixed ...$messages Information/Objects to log (can be of any type)
     */
    public function debug(mixed ...$messages): void
    {
        $this->log("debug", ...$messages);
    }

    /**
     * Log a "info" level line
     * @param mixed ...$messages Information/Objects to log (can be of any type)
     */
    public function info(mixed ...$messages): void
    {
        $this->log("info", ...$messages);
    }

    /**
     * Log a "notice" level line
     * @param mixed ...$messages Information/Objects to log (can be of any type)
     */
    public function notice(mixed ...$messages): void
    {
        $this->log("notice", ...$messages);
    }

    /**
     * Log a "warning" level line
     * @param mixed ...$messages Information/Objects to log (can be of any type)
     */
    public function warning(mixed ...$messages): void
    {
        $this->log("warning", ...$messages);
    }

    /**
     * Log a "error" level line
     * @param mixed ...$messages Information/Objects to log (can be of any type)
     */
    public function error(mixed ...$messages): void
    {
        $this->log("error", ...$messages);
    }

    /**
     * Log a "critical" level line
     * @param mixed ...$messages Information/Objects to log (can be of any type)
     */
    public function critical(mixed ...$messages): void
    {
        $this->log("critical", ...$messages);
    }

    /**
     * Log a "alert" level line
     * @param mixed ...$messages Information/Objects to log (can be of any type)
     */
    public function alert(mixed ...$messages): void
    {
        $this->log("alert", ...$messages);
    }

    /**
     * Log a "emergency" level line
     * @param mixed ...$messages Information/Objects to log (can be of any type)
     */
    public function emergency(mixed ...$messages): void
    {
        $this->log("emergency", ...$messages);
    }
}