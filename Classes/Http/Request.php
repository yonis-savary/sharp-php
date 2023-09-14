<?php

namespace Sharp\Classes\Http;

use Sharp\Classes\Http\Classes\UploadFile;
use Sharp\Classes\Web\Route;
use Sharp\Classes\Core\Logger;
use Sharp\Core\Utils;

/**
 * This component purpose is to hold informations about a HTTP Request,
 * a default one can be built with `Request::buildFromGlobals()`
 */
class Request
{
    protected array $slugs = [];
    protected ?Route $route = null;

    /**
     * @param string $method HTTP Method (GET, POST...)
     * @param string $path Request URI
     * @param array $get GET Params Data
     * @param array $post POST Params Data
     * @param array $uploads Raw PHP Uploads
     * @param array<string,string> $headers Associative Headers (name=>value)
     */
    public function __construct(
        protected string $method,
        protected string $path,
        protected array $get=[],
        protected array $post=[],
        protected array $uploads=[],
        protected array $headers=[],
        protected mixed $body=null
    )
    {
        $this->path = preg_replace("/\?.*/", "", $this->path);
        $this->uploads = $this->getCleanUploadData($uploads);
    }

    /**
     * Build a Request object from PHP's global variables and return it
     */
    public static function buildFromGlobals(): Request
    {
        $headers = [];
        if (function_exists('getallheaders'))
            $headers = getallheaders();

        $postBody = $_POST;

        $body = file_get_contents('php://input');

        if (($headers['Content-Type'] ?? null) === 'application/json')
            $postBody = json_decode($body, true, JSON_THROW_ON_ERROR);

        $request = new self (
            $_SERVER['REQUEST_METHOD'] ?? php_sapi_name(),
            $_SERVER['REQUEST_URI'] ?? '',
            $_GET,
            $postBody,
            $_FILES,
            $headers,
            $body
        );

        return $request;
    }

    /**
     * Log both the http method and path to given Logger (or global instance)
     *
     * @param Logger $logger Logger to log to (global instance if `null`)
     */
    public function logSelf(Logger $logger=null): void
    {
        $logger ??= Logger::getInstance();
        $logger->info(sprintf("Request: %s %s", $this->getMethod(), $this->getPath()));
    }

    protected function getCleanUploadData(array $data): array
    {
        $cleanedUploads = [];

        foreach($data as $inputName => $filedata)
        {
            $toAdd = [];
            if (!is_array($filedata["name"]))
            {
                $toAdd[] = $filedata;
            }
            else
            {
                $keys = array_keys($filedata);
                for ($i=0; $i<count($filedata['name']); $i++)
                {
                    $values = array_map( fn($arr) => $arr[$i], $filedata);
                    $toAdd[] = array_combine($keys, $values);
                }
            }

            foreach ($toAdd as &$upload)
                $upload = new UploadFile($upload, $inputName);

            array_push($cleanedUploads, ...$toAdd);
        }

        return $cleanedUploads;
    }

    /**
     * @return array Array from POST data
     */
    public function post(): array { return $this->post; }

    /**
     * @return array Array from GET data
     */
    public function get() : array { return $this->get; }

    /**
     * @return array Array from both GET and POST data
     */
    public function all() : array { return array_merge($this->post, $this->get); }

    /**
     * Return raw request's body (`php://input`), useful for octet-stream requests
     */
    public function body(): mixed { return $this->body; }

    /**
     * This function can be used with PHP's list function
     *
     * ```php
     * list($login, $password) = $request->list("login", "password");
     * ```
     *
     * @return array Requested parameters in an array
     */
    public function list(string ...$keys): array
    {
        return array_values($this->params($keys));
    }

    /**
     * Retrieve one or more parameters from the request
     * - If one parameter is requested, the function return either `null` or the value
     * - If more parameters are requested, the function return an associative array as `paramName` => value or null
     * @note This function retrieve parameters from both GET and POST data, to retrieve from one `paramsFromGet()` or `paramsFromPost()`
     */
    public function params(string|array $keys): mixed
    {
        return $this->retrieveParams($keys, $this->all());
    }

    /**
     * Same as `params()`, but only retrieve from GET data
     */
    public function paramsFromGet(string|array $keys): mixed
    {
        return $this->retrieveParams($keys, $this->get());
    }

    /**
     * Same as `params()`, but only retrieve from POST data
     */
    public function paramsFromPost(string|array $keys): mixed
    {
        return $this->retrieveParams($keys, $this->post());
    }

    protected function retrieveParams(string|array $keys, array $storage): mixed
    {
        if (!is_array($keys))
            return $storage[$keys] ?? null;

        $results = [];
        foreach ($keys as $k)
            $results[$k] = $storage[$k] ?? null;

        return $results;
    }

    /**
     * @return string HTTP Method
     */
    public function getMethod(): string { return $this->method; }

    /**
     * @return string Request path WITHOUT any GET parameters (pathname)
     */
    public function getPath(): string { return $this->path; }

    /**
     * @return array<string,string> An associative array as `header-name => value`
     */
    public function getHeaders(): array { return $this->headers; }

    /**
     * @return array<UploadFile>
     */
    public function getUploads(): array { return $this->uploads; }

    /**
     * @note !TEST-PURPOSE-METHOD!
     */
    public function setUploads(UploadFile ...$uploads): void { $this->uploads = $uploads; }

    public function setSlugs(array $slugs): void { $this->slugs = $slugs; }

    public function getSlugs(): array { return $this->slugs; }

    public function getSlug(string $key, mixed $default=null) : mixed
    {
        return array_key_exists($key, $this->slugs) ? $this->slugs[$key]:  $default;
    }

    /**
     * Associate a route to the request object
     * (To retrieve it in a controller for example)
     */
    public function setRoute(Route $route) { $this->route = $route; }

    public function getRoute(): ?Route { return $this->route; }

    /**
     * Unset parameters from both GET and POST data
     */
    public function unset(array|string $keys): void
    {
        foreach (Utils::toArray($keys) as $k)
        {
            unset($this->post[$k]);
            unset($this->get[$k]);
        }
    }

    protected function parseHeaders(string $headers)
    {
        $headers = explode("\n", $headers);
        $headers = array_filter($headers, fn($line) => preg_match('/^.+:.+$/', $line));
        $headers = array_map(fn($line) => preg_replace("/\r$/", '', $line), $headers);
        $headers = array_map(fn($line) => explode(':', $line, 2), $headers);

        $assocHeaders = array_combine(
            array_map(fn($e) => strtolower(trim($e[0] ?? null)), $headers),
            array_map(fn($e) => trim($e[1] ?? null), $headers)
        );
        return $assocHeaders;
    }

    /**
     * Fetch a Request target with Curl !
     * @param Logger $logger Optionnal Logger that can be used to log info about the request/response
     * @param int $timeout Optionnal request timeout (seconds)
     * @param string $userAgent User-agent to use with curl
     * @param bool $supportRedirection If `true`, `fetch()` will follow redirect responses
     * @throws \JsonException Possibly when parsing the response body if fetched JSON is incorrect
     */
    public function fetch(
        Logger $logger=null,
        int $timeout=null,
        string $userAgent='Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/112.0',
        bool $supportRedirection=true
    ): Response
    {
        $logger ??= new Logger(null);

        $url = $this->getPath();
        $method = $this->getMethod();
        $headers = $this->getHeaders();
        $post = $this->post();
        $get = http_build_query($this->get(), "?", ";");

        $headers['User-Agent'] = $userAgent;
        foreach ($headers as $key => &$value)
            $value = "$key: $value";
        $requestHeaderString = array_values($headers);

        $handle = curl_init($url . $get);

        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($handle, CURLOPT_HTTPHEADER, $requestHeaderString);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_HEADER, true);

        if ($post && count($post))
            curl_setopt($handle, CURLOPT_POSTFIELDS, $post);
        if ($timeout)
            curl_setopt($handle, CURLOPT_TIMEOUT, $timeout);

        $logger->info("$method $url");
        $result = curl_exec($handle);

        $headerSize = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
        $resStatus = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        $resHeaders = substr($result, 0, $headerSize);
        $resHeaders = $this->parseHeaders($resHeaders);

        if ($supportRedirection && $redirection = $resHeaders['location'] ?? null)
        {
            $request = new self("GET", $redirection);
            return $request->fetch(
                $logger,
                $timeout,
                $userAgent,
                $supportRedirection
            );
        }

        $contentType = $resHeaders['content-type'] ?? null;

        $logger->info($resStatus . ' '. ($contentType??'Unknown') .  ' - ' .strlen($result). ' bytes');

        $resBody = substr($result, $headerSize);
        if (str_starts_with($contentType ?? "", 'application/json'))
            $resBody = json_decode($resBody, true, flags: JSON_THROW_ON_ERROR);

        return new Response($resBody, $resStatus, $resHeaders);
    }
}