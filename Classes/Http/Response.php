<?php

namespace Sharp\Classes\Http;

use InvalidArgumentException;
use Sharp\Classes\Core\Logger;
use Sharp\Classes\Http\Classes\ResponseCodes;
use Sharp\Classes\Web\Renderer;

/**
 * Credit to [developer.mozilla.org](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status) for the Status descriptions
 */
class Response
{
    /**
     * @var array (`NULL` is NOT supported as it can represent an absence of function response !)
     */
    const ADAPT_SUPPORTED_TYPES = ['boolean', 'integer', 'double', 'string', 'array', 'object'];

    protected $content;
    protected int $responseCode = ResponseCodes::OK;
    protected array $headers=[];
    protected array $headersToRemove = [];
    protected $responseTransformer = null;

    /**
     * @note The content value should not be altered
     * @param mixed $content Response content to display (If object, see `$responseTransformer` parameter)
     * @param int $responseCode HTTP Status code (https://developer.mozilla.org/en-US/docs/Web/HTTP/Status)
     * @param array $headers Associative array as `header-name => value`
     * @param callable $responseTransformer Callback that can transform the `$content` object to string
     */
    public function __construct(
        mixed $content=null,
        int $responseCode=ResponseCodes::NO_CONTENT,
        array $headers=[],
        callable $responseTransformer=null
    ) {
        $this->content = $content;
        $this->responseCode = $responseCode;
        $this->withHeaders($headers);
        $this->responseTransformer = $responseTransformer;
    }

    /**
     * Log both the response code and content type to given Logger (or global instance)
     *
     * @param Logger $logger Logger to log to (global instance if `null`)
     */
    public function logSelf(Logger $logger=null): void
    {
        $logger ??= Logger::getInstance();
        $logger->info($this->responseCode . " ". ($this->headers["content-type"] ?? "Unknown MIME"));
    }

    /**
     * @return mixed Raw content as given in the constructor
     */
    public function getContent(): mixed
    {
        return $this->content;
    }

    /**
     * @return int HTTP Response code
     */
    public function getResponseCode(): int
    {
        return $this->responseCode;
    }

    /**
     * @return string Transformed header name to lower case
     * @example NULL `headerName("Content-Type") // returns "content-type"`
     */
    protected function headerName(string $original): string
    {
        return trim(strtolower($original));
    }

    /**
     * Add/Overwrite headers
     * @param array<string,mixed> $headers Associative array as `header-name => value`
     */
    public function withHeaders(array $headers): Response
    {
        $addedHeaders = [];
        foreach ($headers as $name => $value)
        {
            $name = $addedHeaders[] = $this->headerName($name);
            $this->headers[$name] = $value;
        }

        $this->headersToRemove = array_diff(
            $this->headersToRemove,
            $addedHeaders
        );

        return $this;
    }

    /**
     * Remove headers with given names
     *
     * @param array $headers Names of the headers to remove (Case insensitive)
     */
    public function removeHeaders(array $headers): Response
    {
        $headers = array_map(fn($x) => $this->headerName($x), $headers);

        array_push($this->headersToRemove, ...$headers);
        foreach ($headers as $headerName)
            unset($this->headers[$headerName]);

        return $this;
    }

    /**
     * @return array<string,string> Associative array as `headerName => value`
     * @note **Header names are converted to lowercase**
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get a header value from its name
     *
     * @param string Header name to retrieve (case-insensitive)
     * @return ?string Header value if defined, `null` otherwise
     */
    public function getHeader(string $headerName): ?string
    {
        return $this->headers[$this->headerName($headerName)] ?? null;
    }

    /**
     * Send headers and display the response content
     * @param bool $sendHeaders If `true`, send the headers, otherwise, only display the content
     */
    public function display(bool $sendHeaders=true): void
    {
        if ($sendHeaders)
        {
            http_response_code($this->responseCode);

            foreach ($this->headers as $name => $value)
                header("$name: $value");

            // @todo Make this an option (configurable)
            $this->removeHeaders(["x-powered-by"]);

            foreach ($this->headersToRemove as $header)
                header_remove($header);
        }

        $toDisplay = $this->content;

        if (str_starts_with($this->headers["content-type"] ?? "", 'application/json'))
            $toDisplay = json_encode($toDisplay, JSON_THROW_ON_ERROR);

        if ($callback = $this->responseTransformer)
            $toDisplay = $callback($this->content);

        if ($toDisplay)
            echo (string)$toDisplay;
    }

    /**
     * Return a new HTML response
     */
    public static function html(string $content, int $responseCode=ResponseCodes::OK): Response
    {
        return new Response($content, $responseCode, ["Content-Type" => "text/html"]);
    }

    /**
     * Return a new download response
     * @param string $file File PATH
     */
    public static function file(string $file, string $attachmentName=null): Response
    {
        if (!is_file($file))
            throw new InvalidArgumentException("Inexistent file [$file] !");

        $attachmentName ??= basename($file);

        return new Response(
            $file,
            ResponseCodes::OK,
            [
                "Content-Description" => "File Transfer",
                "Content-Type" => "application/octet-stream",
                "Content-Disposition" => "attachment; filename=$attachmentName",
                "Expires" => "0",
                "Cache-Control" => "must-revalidate",
                "Pragma" => "public",
                "Content-Length" => filesize($file),
            ],
            function() use ($file){
                readfile($file);
            }
        );
    }

    /**
     * Build a JSON response
     * @param mixed $content Raw content, don't transform it into string before calling this function
     */
    public static function json(mixed $content, int $responseCode=ResponseCodes::OK): Response
    {
        return new Response($content, $responseCode, ["Content-Type" => "application/json"]);
    }

    /**
     * Build a response that redirect the user
     */
    public static function redirect(string $location, int $responseCode=ResponseCodes::SEE_OTHER): Response
    {
        return new Response(null, $responseCode, ["Location" => $location]);
    }

    public static function render(string $template, array $context=[], int $responseCode=ResponseCodes::OK): Response
    {
        return self::html(Renderer::getInstance()->render($template, $context), $responseCode);
    }

    /**
     * Give an object to this method to get a Response in any case
     * - If `null` is given, a 204 Response is given and you are warned in the logs
     * - If a response is given, nothing change and it is returned
     * - Otherwise, a JSON response containing the object is returned
     */
    public static function adapt(mixed $content): Response
    {
        if ($content instanceof Response)
            return $content;

        if (is_null($content))
            return new Response(null, 204);

        $contentType = gettype($content);
        if (!in_array($contentType, self::ADAPT_SUPPORTED_TYPES))
        {
            Logger::getInstance()->warning(new InvalidArgumentException(
                "A response with an unsupported type ($contentType) was returned and cannot be adapted"
            ));
            return new Response(null, 204);
        }

        return self::json($content);
    }
}