[< Back to summary](../README.md)

# 📨 Requests and Responses

[`Request`](../../Classes/Http/Request.php) and [`Response`](../../Classes/Http/Response.php) classes are basics data structure to interact with input/output, both of them are made of getters/setters with a few additional features

> [!NOTE]
> In an effort to normalize header names,
> each Response/Request header name is transformed to its lowercase equivalent

## Basic Requests Usage

### Request Creation

In most cases, you won't have the need to create a Request

```php
// Build from PHP Global variables
$request = Request::buildFromGlobals();

// Manually build a response
// $get, $post, $headers must be associative
new Request(
    protected string $method,
    protected string $path,
    protected array $get=[],
    protected array $post=[],
    protected array $uploads=[],
    protected array $headers=[]
);
```

### Retrieving Parameters & Body

```php
$POST = $request->post(); // Get POST data
$GET  = $request->get();  // Get GET data
$ALL  = $request->all(); // Get POST + GET merged data (GET is considered more important)
$body = $request->body(); // Get JSON Body

// Params read parameters from both POST and GET sources
// Get a value or null if inexistent
$password = $request->params("password");
// Get an associative array
$creds = $request->params(["username", "password"]);

// $request->paramsFromGet(); Same as params but only takes values from GET
// $request->paramsFromPost(); Same as params but only takes values from POST

// Alternative to list($username, $password) = array_values($request->params(["username", "password"]))
list($username, $password) = $request->list("username", "password");

// Delete parameters from both POST and GET data
$request->unset(["username", "password"]);


// Requests got a validate() method to retrieve parameters and check them at the same time
list(
    $id,
    $newName
) = $request->validate([
    "id" => Request::INT | Request::NOT_NULL,
    "newName" => Request::IS_STRING | Request::NOT_NULL
]);

list(
    $success,
    $values,
    $errors
) = $request->validate([
    "id" => Request::INT | Request::NOT_NULL,
    "newName" => Request::IS_STRING | Request::NOT_NULL
], false);
```


### Getters/Setters

```php
$request->getMethod(); // Get HTTP Method
$request->getPath(); // Get pathname (Without GET parameters)
$request->getHeaders(); // Get an associative array with header-name => HeaderValue

$request->setSlugs(); // Set slugs values
$request->getSlugs(); // Get slugs values
$request->getSlug(); // Get one specific value from slugs

// Associate a route to the request, so it can be retrieve later
$request->setRoute();
// Get the associated route object
$request->getRoute();

$request->getUploads(); // Get an array of UploadFile

```

## Basic Responses Usage

### Response creation

```php
// Generate an HTML response
$response = Response::html("<html>...");

// Generate a JSON response
$response = Response::json($myObject);

// Generate a Response that transfer a file to the client
$response = Response::file("/path/to/my/file.txt");

// Generate a Response that redirect the Client
$response = Response::redirect("/another/url");

// Try to make a JSON Response of whatever is given to it
$response = Response::adapt($anyObject);

// Render a view (with optionnal context) and return a HTML Response
$response = Response::view("myView", $contextData);

// Manually create a Response
new Response(
    mixed $content=null, // Raw content, can be an object a string, an array...etc
    int $responseCode=self::NO_CONTENT, // HTTP Response code
    array $headers=[], // Associative Headers array
    callable $responseTransformer=null
);
// $responseTransformer is a function that
// can generate a string to display to the client
// (it is called if given content is null)

```

### Response interaction

```php
# Get raw content object
$content = $response->getContent();

# Log the response code and content type to any Logger
# The global instance is used by default
$response->logSelf($logger);

# Add/Overwrite headers into the Response
$response->withHeaders(["Content-Type" => "text/html"]);
# Get an associative headers array
$response->getHeaders();
# Header value or null
$response->getHeader('content-type');

# Display the response to the client
$response->display();
# Only display the content, skip the headers
$response->display(false);
```


## Request Configuration

Here is the default configuration for `Request`

```json
"request": {
    "typed-parameters": true
}
```

`typed-parameters` means that every `GET` parameters that comes as a string will be interpreted.
When this parameter is enabled `Request` tried to interpret Boolean & Null values coming from `$_GET`

| Origin                               | Transformed       |
|--------------------------------------|-------------------|
| `"true"`, `"TRUE"`, `"True"`, ...    | `true` (boolean)  |
| `"false"`, `"FALSE"`, `"False"`, ... | `false` (boolean) |
| `"null"`, `"NULL"`, `"Null"`, ...    | `null`            |


## Advanced Request Usage (cURL !)

The request class got the `fetch` method, which can be
used to fetch content with the `curl` extension !

```php
$request = new Request("GET", "https://google.com");

$response = $request->fetch(); // fetch() return a Response object

$headers = $response->getHeaders();
$content = $response->getContent();
```

You can give some parameters to this function
to customize its behavior

```php
// $logger: A Logger can be given to log debug information
// $timeout: If given, will given the CURL handler a max timeout
// $userAgent: The default userAgent can be overwritten too
// $supportRedirection: If `true`, fetch() will follow 3XX response and return the final response

$request->fetch(
    Logger $logger=null,
    int $timeout=null,
    ?string $userAgent='...',
    bool $supportRedirection=true,
    int $logFlags=self::DEBUG_ESSENTIALS
);
```

### fetch debugging

Different log flags can be given to `fetch`, you can use them to choose which information you want to trace

| Flag                     | Purpose                |
| -------------------------|------------------------|
| `DEBUG_REQUEST_CURL`     | Log infos on every `curl_setopt` call |
| `DEBUG_REQUEST_HEADERS`  | Log infos on the Request's headers |
| `DEBUG_REQUEST_BODY`     | Log Request's GET, POST and body data |
| `DEBUG_REQUEST`          | Log both Request's header and data |
| `DEBUG_RESPONSE_HEADERS` | Log the Response's headers |
| `DEBUG_RESPONSE_BODY`    | Log the Response's raw body |
| `DEBUG_RESPONSE`         | Log everything about the response |
| `DEBUG_ESSENTIALS`       | `DEBUG_REQUEST_HEADERS` + `DEBUG_RESPONSE_HEADERS`; |
| `DEBUG_ALL`              | Log everything above |

Example:
```php
$request->fetch(..., logFlags: Request::DEBUG_REQUEST_HEADERS | Request::DEBUG_RESPONSE_BODY);
```

[< Back to summary](../README.md)