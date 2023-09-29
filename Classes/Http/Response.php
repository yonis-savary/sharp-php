<?php

namespace Sharp\Classes\Http;

use InvalidArgumentException;
use Sharp\Classes\Core\Logger;

/**
 * Credit to [developer.mozilla.org](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status) for the Status descriptions
 */
class Response
{

    /**
     * The request succeeded. The result meaning of "success" depends on the HTTP method:
     *     - GET: The resource has been fetched and transmitted in the message body.
     *     - HEAD: The representation headers are included in the response without any message body.
     *     - PUT or POST: The resource describing the result of the action is transmitted in the message body.
     *     - TRACE: The message body contains the request message as received by the server.
     */
    const OK = 200;

    /** The request succeeded, and a new resource was created as a result. This is typically the response sent after POST requests, or some PUT requests. */
    const CREATED = 201;

    /** The request has been received but not yet acted upon. It is noncommittal, since there is no way in HTTP to later send an asynchronous response indicating the outcome of the request. It is intended for cases where another process or server handles the request, or for batch processing. */
    const ACCEPTED = 202;

    /** This response code means the returned metadata is not exactly the same as is available from the origin server, but is collected from a local or a third-party copy. This is mostly used for mirrors or backups of another resource. Except for that specific case, the 200 OK response is preferred to this status. */
    const NON_AUTHORITATIVE_INFORMATION = 203;

    /** There is no content to send for this request, but the headers may be useful. The user agent may update its cached headers for this resource with the new ones. */
    const NO_CONTENT = 204;

    /** Tells the user agent to reset the document which sent this request. */
    const RESET_CONTENT = 205;

    /** This response code is used when the Range header is sent from the client to request only part of a resource. */
    const PARTIAL_CONTENT = 206;

    /** Conveys information about multiple resources, for situations where multiple status codes might be appropriate. */
    const MULTI_STATUS = 207;

    /** Used inside a response element to avoid repeatedly enumerating the internal members of multiple bindings to the same collection. */
    const ALREADY_REPORTED = 208;

    /** The server has fulfilled a GET request for the resource, and the response is a representation of the result of one or more instance-manipulations applied to the current instance. */
    const IM_USED = 226;

    /** The request has more than one possible response. The user agent or user should choose one of them. (There is no standardized way of choosing one of the responses, but HTML links to the possibilities are recommended so the user can pick.) */
    const MULTIPLE_CHOICES = 300;

    /** The URL of the requested resource has been changed permanently. The new URL is given in the response. */
    const MOVED_PERMANENTLY = 301;

    /** This response code means that the URI of requested resource has been changed temporarily. Further changes in the URI might be made in the future. Therefore, this same URI should be used by the client in future requests. */
    const FOUND = 302;

    /** The server sent this response to direct the client to get the requested resource at another URI with a GET request. */
    const SEE_OTHER = 303;

    /** This is used for caching purposes. It tells the client that the response has not been modified, so the client can continue to use the same cached version of the response. */
    const NOT_MODIFIED = 304;

    /** The server sends this response to direct the client to get the requested resource at another URI with the same method that was used in the prior request. This has the same semantics as the 302 Found HTTP response code, with the exception that the user agent must not change the HTTP method used: if a POST was used in the first request, a POST must be used in the second request. */
    const TEMPORARY_REDIRECT = 307;

    /** This means that the resource is now permanently located at another URI, specified by the Location: HTTP Response header. This has the same semantics as the 301 Moved Permanently HTTP response code, with the exception that the user agent must not change the HTTP method used: if a POST was used in the first request, a POST must be used in the second request. */
    const PERMANENT_REDIRECT = 308;

    /** The server cannot or will not process the request due to something that is perceived to be a client error (e.g., malformed request syntax, invalid request message framing, or deceptive request routing). */
    const BAD_REQUEST = 400;

    /** Although the HTTP standard specifies "unauthorized", semantically this response means "unauthenticated". That is, the client must authenticate itself to get the requested response. */
    const UNAUTHORIZED = 401;

    /** The client does not have access rights to the content; that is, it is unauthorized, so the server is refusing to give the requested resource. Unlike 401 Unauthorized, the client's identity is known to the server. */
    const Forbidden = 403;

    /** The server cannot find the requested resource. In the browser, this means the URL is not recognized. In an API, this can also mean that the endpoint is valid but the resource itself does not exist. Servers may also send this response instead of 403 Forbidden to hide the existence of a resource from an unauthorized client. This response code is probably the most well known due to its frequent occurrence on the web. */
    const NOT_FOUND = 404;

    /** The request method is known by the server but is not supported by the target resource. For example, an API may not allow calling DELETE to remove a resource. */
    const METHOD_NOT_ALLOWED = 405;

    /** This response is sent when the web server, after performing server-driven content negotiation, doesn't find any content that conforms to the criteria given by the user agent. */
    const NOT_ACCEPTABLE = 406;

    /** This is similar to 401 Unauthorized but authentication is needed to be done by a proxy. */
    const PROXY_AUTHENTICATION_REQUIRED = 407;

    /** This response is sent on an idle connection by some servers, even without any previous request by the client. It means that the server would like to shut down this unused connection. This response is used much more since some browsers, like Chrome, Firefox 27+, or IE9, use HTTP pre-connection mechanisms to speed up surfing. Also note that some servers merely shut down the connection without sending this message. */
    const REQUEST_TIMEOUT = 408;

    /** This response is sent when a request conflicts with the current state of the server. */
    const CONFLICT = 409;

    /** This response is sent when the requested content has been permanently deleted from server, with no forwarding address. Clients are expected to remove their caches and links to the resource. The HTTP specification intends this status code to be used for "limited-time, promotional services". APIs should not feel compelled to indicate resources that have been deleted with this status code. */
    const GONE = 410;

    /** Server rejected the request because the Content-Length header field is not defined and the server requires it. */
    const LENGTH_REQUIRED = 411;

    /** The client has indicated preconditions in its headers which the server does not meet. */
    const PRECONDITION_FAILED = 412;

    /** Request entity is larger than limits defined by server. The server might close the connection or return an Retry-After header field. */
    const PAYLOAD_TOO_LARGET = 413;

    /** The URI requested by the client is longer than the server is willing to interpret. */
    const URI_TOO_LONG = 414;

    /** The media format of the requested data is not supported by the server, so the server is rejecting the request. */
    const UNSOPPORTED_MEDIA_TYPE = 415;

    /** The range specified by the Range header field in the request cannot be fulfilled. It's possible that the range is outside the size of the target URI's data. */
    const RANGE_NOT_SATISFIABLE = 416;

    /** This response code means the expectation indicated by the Expect request header field cannot be met by the server. */
    const EXPECTATION_FAILED = 417;

    /** The server refuses the attempt to brew coffee with a teapot. */
    const IM_A_TEAPOT = 418;

    /** The request was directed at a server that is not able to produce a response. This can be sent by a server that is not configured to produce responses for the combination of scheme and authority that are included in the request URI. */
    const MISDIRECTED_REQUEST = 421;

    /** The request was well-formed but was unable to be followed due to semantic errors. */
    const UNPROCESSABLE_CONTENT = 422;

    /** The resource that is being accessed is locked. */
    const LOCKED = 423;

    /** The request failed due to failure of a previous request. */
    const FAILED_DEPENDENCY = 424;

    /** The server refuses to perform the request using the current protocol but might be willing to do so after the client upgrades to a different protocol. The server sends an Upgrade header in a 426 response to indicate the required protocol(s). */
    const UPGRADE_REQUIRED = 426;

    /** The origin server requires the request to be conditional. This response is intended to prevent the 'lost update' problem, where a client GETs a resource's state, modifies it and PUTs it back to the server, when meanwhile a third party has modified the state on the server, leading to a conflict. */
    const PRECONDITION_REQUIRED = 428;

    /** The user has sent too many requests in a given amount of time ("rate limiting"). */
    const TOO_MANY_REQUESTS = 429;

    /** The server is unwilling to process the request because its header fields are too large. The request may be resubmitted after reducing the size of the request header fields. */
    const REQUEST_HEADER_FIELDS_TOO_LARGET = 431;

    /** The user agent requested a resource that cannot legally be provided, such as a web page censored by a government. */
    const UNAVAILABLE_FOR_LEGAL_REASONS = 451;

    /** The server has encountered a situation it does not know how to handle. */
    const INTERNAL_SERVER_ERROR = 500;

    /** The request method is not supported by the server and cannot be handled. The only methods that servers are required to support (and therefore that must not return this code) are GET and HEAD. */
    const NOT_IMPLEMENTED = 501;

    /** This error response means that the server, while working as a gateway to get a response needed to handle the request, got an invalid response. */
    const BAD_GATEWAY = 502;

    /** The server is not ready to handle the request. Common causes are a server that is down for maintenance or that is overloaded. Note that together with this response, a user-friendly page explaining the problem should be sent. This response should be used for temporary conditions and the Retry-After HTTP header should, if possible, contain the estimated time before the recovery of the service. The webmaster must also take care about the caching-related headers that are sent along with this response, as these temporary condition responses should usually not be cached. */
    const SERVICE_UNAVAILABLE = 503;

    /** This error response is given when the server is acting as a gateway and cannot get a response in time. */
    const GATEWAY_TIMEOUT = 504;

    /** The HTTP version used in the request is not supported by the server. */
    const HTTP_VERSION_NOT_SUPPORTED = 505;

    /** The server has an internal configuration error: the chosen variant resource is configured to engage in transparent content negotiation itself, and is therefore not a proper end point in the negotiation process. */
    const VARIANT_ALSO_NEGOTIATES = 506;

    /** The method could not be performed on the resource because the server is unable to store the representation needed to successfully complete the request. */
    const INSUFFICIENT_STORAGE = 507;

    /** The server detected an infinite loop while processing the request. */
    const LOOP_DETECTED = 508;

    /** Further extensions to the request are required for the server to fulfill it. */
    const NOT_EXTENDED = 510;

    /** Indicates that the client needs to authenticate to gain network access. */
    const NETWORK_AUTHENTICATION_REQUIRED = 511;

    /** @var array (`NULL` is NOT supported as it can represent an absence of function response !) */
    const ADAPT_SUPPORTED_TYPES = ['boolean', 'integer', 'double', 'string', 'array', 'object'];

    protected $content;
    protected int $responseCode = self::OK;
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
        int $responseCode=self::NO_CONTENT,
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
        return strtolower($original);
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
            echo "$toDisplay";
    }

    /**
     * Return a new HTML response
     */
    public static function html(string $content, int $responseCode=self::OK): Response
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
            throw new InvalidArgumentException("Inexistant file [$file] !");

        $attachmentName ??= basename($file);

        return new Response(
            null,
            self::OK,
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
    public static function json(mixed $content, int $responseCode=self::OK): Response
    {
        return new Response($content, $responseCode, ["Content-Type" => "application/json"]);
    }

    /**
     * Build a response that redirect the user
     */
    public static function redirect(string $location, int $responseCode=self::SEE_OTHER): Response
    {
        return new Response(null, $responseCode, ["Location" => $location]);
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
            Logger::getInstance()->logThrowable(new InvalidArgumentException(
                "A reponse with an unsupported type ($contentType) was returned and cannot be adapted"
            ));
            return new Response(null, 204);
        }

        return self::json($content);
    }
}