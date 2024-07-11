<?php

namespace Sharp\Classes\Http;

use CurlHandle;
use InvalidArgumentException;
use RuntimeException;
use Sharp\Classes\Core\Configurable;
use Sharp\Classes\Http\Classes\UploadFile;
use Sharp\Classes\Web\Route;
use Sharp\Classes\Core\Logger;
use Sharp\Classes\Data\ObjectArray;
use Sharp\Classes\Http\Classes\ResponseCodes;
use Sharp\Core\Utils;
use Stringable;

/**
 * This component purpose is to hold information about a HTTP Request,
 * a default one can be built with `Request::buildFromGlobals()`
 */
class Request
{
    use Configurable;

    protected array $slugs = [];
    protected ?Route $route = null;

    const IS_INT     = 0b000000000001;
    const IS_FLOAT   = 0b000000000010;
    const IS_STRING  = 0b000000000100;
    const IS_EMAIL   = 0b000000001000;
    const IS_BOOLEAN = 0b000000010000;
    const IS_URL     = 0b000000100000;
    const IS_MAC     = 0b000001000000;
    //const IS_DOMAIN  = 0b000010000000;
    const IS_IP      = 0b000100000000;
    const IS_REGEXP  = 0b001000000000;
    const NOT_NULL   = 0b100000000000;

    const DEBUG_REQUEST_CURL     = 0b0000_0001;
    const DEBUG_REQUEST_HEADERS  = 0b0000_0010;
    const DEBUG_REQUEST_BODY     = 0b0000_0100;
    const DEBUG_REQUEST          = 0b0000_1111;

    const DEBUG_RESPONSE_HEADERS = 0b0001_0000;
    const DEBUG_RESPONSE_BODY    = 0b0010_0000;
    const DEBUG_RESPONSE         = 0b1111_0000;

    const DEBUG_ESSENTIALS       = self::DEBUG_REQUEST_HEADERS | self::DEBUG_RESPONSE_HEADERS;
    const DEBUG_ALL              = 0b1111_1111;

    public static function getDefaultConfiguration(): array
    {
        return ["typed-parameters" => true];
    }

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
        protected mixed $body=null,
        protected ?string $ip=null
    )
    {
        $this->path = preg_replace("/\?.*/", "", $this->path);
        $this->uploads = $this->getCleanUploadData($uploads);
        $this->body = $body;

        $this->headers = array_change_key_case($this->headers, CASE_LOWER);

        if ($this->isJSON())
            $this->body = json_decode($this->body ?? "null", true, JSON_THROW_ON_ERROR);

        if ($this->body === "")
            $this->body = null;
    }

    /**
     * This function's purpose is to fix types of GET and POST parameters
     * when getting a `"null"`, value, we can assume it is a `null` in reality
     * (Same for `"true"` and `"false"`)
     */
    protected static function parseDictionaryValueTypes(array $dict)
    {
        foreach ($dict as $_ => &$value)
        {
            if (!($value instanceof Stringable || is_string($value)))
                continue;

            $lower = strtolower("$value");

            if ($lower === "null")
                $value = null ;
            else if ($lower === "false")
                $value = false;
            else if ($lower === "true")
                $value = true;
        }
        return $dict;
    }

    /**
     * Build a Request object from PHP's global variables and return it
     */
    public static function buildFromGlobals(bool $fixParametersTypes=true): Request
    {
        $headers = function_exists("getallheaders") ?
            getallheaders() :
            [];

        $get = $_GET;
        $post = $_POST;

        if ($fixParametersTypes === true)
        {
            $get = self::parseDictionaryValueTypes($get);
            $post = self::parseDictionaryValueTypes($post);
        }

        $path = $_SERVER['REQUEST_URI'] ?? "";
        if (str_ends_with($path, "/"))
            $path = substr($path, 0, strlen($path)-1);

        $request = new self (
            $_SERVER['REQUEST_METHOD'] ?? php_sapi_name(),
            $path,
            $get,
            $post,
            $_FILES,
            $headers,
            file_get_contents('php://input'),
            $_SERVER["REMOTE_ADDR"] ?? null
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
        $logger->info("Request: ". $this->getMethod() . " " . $this->getPath());
    }

    protected function getCleanUploadData(array $data): array
    {
        $cleanedUploads = [];

        foreach($data as $inputName => $fileData)
        {
            $toAdd = [];
            if (!is_array($fileData["name"]))
            {
                $toAdd[] = $fileData;
            }
            else
            {
                $keys = array_keys($fileData);
                for ($i=0; $i<count($fileData['name']); $i++)
                {
                    $values = array_map( fn($arr) => $arr[$i], $fileData);
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
    public function post(): array
    {
        return $this->post;
    }

    /**
     * @return array Array from GET data
     */
    public function get() : array
    {
        return $this->get;
    }

    /**
     * @return array Array from both GET and POST data
     */
    public function all() : array
    {
        return array_merge($this->post, $this->get);
    }

    /**
     * @return mixed Raw request's body (`php://input`), useful for octet-stream requests
     */
    public function body(): mixed
    {
        return $this->body;
    }

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
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string Request path WITHOUT any GET parameters (pathname)
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return array<string,string> An associative array as `header-name => value`
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    /**
     * @param string $name If specified, only uploads with the given form/input name are returned
     * @return array<UploadFile>
     */
    public function getUploads(string $name=null): array
    {
        if (!$name)
            return $this->uploads;

        return (new ObjectArray($this->uploads))
        ->filter(fn(UploadFile $file) => $file->getInputName() === $name)
        ->collect();
    }

    /**
     * Test-purpose method
     */
    public function setUploads(UploadFile ...$files): void
    {
        $this->uploads = $files;
    }

    public function setSlugs(array $slugs): void
    {
        $this->slugs = $slugs;
    }

    public function getSlugs(): array
    {
        return $this->slugs;
    }

    public function getSlug(string $key, mixed $default=null) : mixed
    {
        return array_key_exists($key, $this->slugs) ?
            $this->slugs[$key]:
            $default;
    }

    /**
     * Associate a route to the request object
     * (To retrieve it in a controller for example)
     */
    public function setRoute(Route $route)
    {
        $this->route = $route;
    }

    public function getRoute(): ?Route
    {
        return $this->route;
    }

    /**
     * Unset parameters from both GET and POST data
     */
    public function unset(array|string $keys): void
    {
        foreach (Utils::toArray($keys) as $k)
            unset(
                $this->post[$k],
                $this->get[$k]
            );
    }

    /**
     * Parse raw HTTP Headers (string)
     * to an associative array of data with `HeaderName => HeaderValue`
     */
    protected function parseHeaders(string $headers): array
    {
        return ObjectArray::fromExplode("\n", $headers)
        ->filter(fn($line) => str_contains($line, ":"))
        ->toAssociative(function($line){
            $line = preg_replace("/\r$/", '', $line);
            list($headerName, $headerValue) = explode(':', $line, 2);
            return [trim($headerName), trim($headerValue)];
        });
    }

    public function isJSON(): bool
    {
        return str_starts_with($this->headers["content-type"] ?? "", "application/json");
    }

    /**
     * Build a cURL handle for the Request object
     *
     * @param ?int $timeout Optional cURL timeout limit (seconds)
     * @param ?string $userAgent Optional cURL user-agent header to use
     * @return CurlHandle Instance containing every request information
     */
    public function toCurlHandle(
        int $timeout=null,
        ?string $userAgent='Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/112.0',
        Logger $logger=null,
        int $logFlags=self::DEBUG_ALL
    ): CurlHandle
    {
        $logger ??= new Logger();
        if (!Utils::valueHasFlag($logFlags, self::DEBUG_REQUEST_CURL))
            $logger = new Logger(); // replace potential logger with null logger

        $logger->info("Building CURL handle");

        $thisGET = $this->get() ?? [];
        $thisPOST = $this->post() ?? [];
        $thisMethod = $this->getMethod();
        $headers = $this->getHeaders();
        $isJSONRequest = $this->isJSON();

        $getParams = count($thisGET) ? http_build_query($this->get(), "?", ";") : "";
        $url = trim($this->getPath() . $getParams);

        $handle = curl_init($url);

        switch (strtoupper($thisMethod))
        {
            case "GET":
                /* GET by default*/ ;
                break;
            case "POST":
                $logger->info("Using CURLOPT_POST");
                curl_setopt($handle, CURLOPT_POST, true);
                break;
            case "HEAD":
                $logger->info("Using CURLOPT_NOBODY");
                curl_setopt($handle, CURLOPT_NOBODY, true);
                break;
            case "PUT":
            case "PATCH":
                $logger->info("Using CURLOPT_PUT");
                curl_setopt($handle, CURLOPT_PUT, true);
                break;
            default:
                $logger->info("Setting CURLOPT_CUSTOMREQUEST to", $thisMethod);
                curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $thisMethod);
                break;
        }

        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_HEADER, true);


        if (count($thisPOST))
        {
            $postFields = $isJSONRequest ?
                json_encode($thisPOST, JSON_THROW_ON_ERROR):
                $thisPOST;

            $logger->info("Setting CURLOPT_POSTFIELDS to", $postFields);
            curl_setopt($handle, CURLOPT_POSTFIELDS, $postFields);
        }

        if ($timeout)
        {
            $logger->info("Setting CURLOPT_CONNECTTIMEOUT, CURLOPT_TIMEOUT to " . $timeout);
            curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($handle, CURLOPT_TIMEOUT, $timeout);
        }

        if ($userAgent)
        {
            $logger->info("Using 'user-agent'", $userAgent);
            $headers['user-agent'] = $userAgent;
        }

        $headersStrings = [];
        foreach ($headers as $key => &$value)
            $headersStrings[] = "$key: $value";

        $logger->info("Setting CURLOPT_HTTPHEADER to", $headersStrings);
        curl_setopt($handle, CURLOPT_HTTPHEADER, $headersStrings);

        return $handle;
    }

    /**
     * Fetch a Request target with Curl !
     * @param Logger $logger Optional Logger that can be used to log info about the request/response
     * @param int $timeout Optional request timeout (seconds)
     * @param string $userAgent User-agent to use with curl
     * @param bool $supportRedirection If `true`, `fetch()` will follow redirect responses
     * @throws \JsonException Possibly when parsing the response body if fetched JSON is incorrect
     */
    public function fetch(
        Logger $logger=null,
        int $timeout=null,
        ?string $userAgent='Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/112.0',
        bool $supportRedirection=true,
        int $logFlags=self::DEBUG_ESSENTIALS
    ): Response|CurlHandle
    {
        $handle = $this->toCurlHandle($timeout, $userAgent, $logger, $logFlags);

        $logger ??= new Logger(null);

        if (Utils::valueHasFlag($logFlags, self::DEBUG_REQUEST_HEADERS))
        {
            $this->logSelf($logger);
            $logger->info($this->getHeaders());
        }

        if (Utils::valueHasFlag($logFlags, self::DEBUG_REQUEST_BODY))
            $logger->info(
                "GET", $this->get(),
                "POST", $this->post(),
                "BODY", $this->body()
            );

        if (!($result = curl_exec($handle)))
            throw new RuntimeException(sprintf("Curl error %s: %s", curl_errno($handle), curl_error($handle)));

        $headerSize = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
        $resStatus = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        if (Utils::valueHasFlag($logFlags, self::DEBUG_RESPONSE_HEADERS))
            $logger->info(sprintf("Got [$resStatus] with [%s] bytes of data", strlen($result)));

        $resHeaders = substr($result, 0, $headerSize);
        $resHeaders = $this->parseHeaders($resHeaders);
        $resHeaders = array_change_key_case($resHeaders, CASE_LOWER);

        if (Utils::valueHasFlag($logFlags, self::DEBUG_RESPONSE_HEADERS))
            $logger->info("Got Headers", $resHeaders);

        if ($supportRedirection && $nextURL = ($resHeaders['location'] ?? null))
        {
            $logger->info("Got redirected to [$nextURL]");
            $request = new self("GET", $nextURL);
            return $request->fetch(
                $logger,
                $timeout,
                $userAgent,
                $supportRedirection
            );
        }

        $resBody = substr($result, $headerSize);

        if (Utils::valueHasFlag($logFlags, self::DEBUG_RESPONSE_BODY))
            $logger->info("Got Body", $resBody);

        if (str_starts_with($resHeaders['content-type'] ?? "", 'application/json'))
        {
            if (Utils::valueHasFlag($logFlags, self::DEBUG_RESPONSE_BODY))
                $logger->info("Decoding JSON body");

            $resBody = json_decode($resBody, true, flags: JSON_THROW_ON_ERROR);
        }

        return new Response($resBody, $resStatus, $resHeaders);
    }


    /**
     * Validate request parameters
     * @param array $requirements Associative array of [name => requirements flags]
     * @param bool $errorMode Disable this to never show error to the client
     * @return array When not in error mode, returns `[isSuccess, values, errors]`
     * If error mode is enabled, return an array of value when successful, display a 400 HTTP Response on error
     */
    public function validate(array $requirements, bool $errorMode=true): array
    {
        if (!Utils::isAssoc($requirements))
            throw new InvalidArgumentException("requirements must be an associative array");

        $paramsToGet = array_keys($requirements);
        $requirements = array_values($requirements);

        $values = $this->list(...$paramsToGet);
        $errors = [];

        $paramsCount = count($paramsToGet);
        for ($i=0; $i<$paramsCount; $i++)
        {
            $requirement = $requirements[$i];
            $name = $paramsToGet[$i];
            $value = $values[$i];

            if (($requirement & self::IS_INT) && (!(is_numeric($value) && filter_var($value, FILTER_VALIDATE_INT))))
                    $errors[] = "[$name] must be an integer";

            if (($requirement & self::IS_FLOAT) && (!(is_numeric($value) && filter_var($value, FILTER_VALIDATE_FLOAT))))
                    $errors[] = "[$name] must be a number";

            if (($requirement & self::IS_STRING) && (!is_string($value)))
                    $errors[] = "[$name] must be a textual value";

            if (($requirement & self::IS_EMAIL) && (!filter_var($value, FILTER_VALIDATE_EMAIL)))
                    $errors[] = "[$name] must be an email";

            if (($requirement & self::IS_BOOLEAN) && (!filter_var($value,  FILTER_VALIDATE_BOOLEAN)))
                    $errors[] = "[$name] must be a boolean";

            if (($requirement & self::IS_URL) && (!filter_var($value,  FILTER_VALIDATE_URL)))
                    $errors[] = "[$name] must be a url";

            if (($requirement & self::IS_MAC) && (!filter_var($value,  FILTER_VALIDATE_MAC)))
                    $errors[] = "[$name] must be a max";

            //if (($requirement & self::IS_DOMAIN) && (!filter_var($value,  FILTER_VALIDATE_DOMAIN)))
            //        $errors[] = "[$name] must be a domain";

            if (($requirement & self::IS_IP) && (!filter_var($value,  FILTER_VALIDATE_IP)))
                    $errors[] = "[$name] must be a ip";

            //if (($requirement & self::IS_REGEXP) && (!filter_var($value,  FILTER_VALIDATE_REGEXP)))
            //        $errors[] = "[$name] must be a regular expression";

            if (($requirement & self::NOT_NULL) && ($value === null))
                    $errors[] = "[$name] parameter is required";

        }

        // faster than count ! Todo: put this in Utils
        $hasError = isset($errors[0]);

        if ($errorMode)
        {
            if ($hasError)
            {
                Response::json($errors, ResponseCodes::BAD_REQUEST)->display();
                die;
            }
            return $values;
        }

        return [!$hasError, $values, $errors];
    }
}