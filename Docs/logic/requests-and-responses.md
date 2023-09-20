[< Back to summary](../home.md)

# 📨 Requests and Responses

[Request](../../Classes/Http/Request.php) and [Response](../../Classes/Http/Response.php) classes are basics data structure to interact with input/output, both of them are made of getters/setters with a few additionnal features

## Basic Requests Usage

```php
// Manually build a response
// $get, $post, $headers must be associatives
new Request(
    protected string $method,
    protected string $path,
    protected array $get=[],
    protected array $post=[],
    protected array $uploads=[],
    protected array $headers=[]
);

// Build from PHP Global variables
$request = Request::buildFromGlobals();

$POST = $request->post(); // Get POST data
$GET  = $request->get();  // Get GET data
$ALL  = $request->all(); // Get POST + GET merged data

// Params read parameters from both POST and GET sources
$params = $request->params(["username", "password"]); // Get an associative array
$params = $request->params(["password"); // Get a value or null if inexistant

// $request->paramsFromGet(); Same as params but only takes values from GET
// $request->paramsFromPost(); Same as params but only takes values from POST

// Alternative to list($username, $password) = array_values($request->params(["username", "password"]))
list($username, $password) = $request->list("username", "password");

// Delete parameters from both POST and GET data
$request->unset(["username", "password"]);

$request->getMethod(); // Get HTTP Method
$request->getPath(); // Get pathname (Without GET parameters)
$request->getHeaders(); // Get an associative array with HeaderName => HeaderValue

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

```php
// Generate an HTML response
$response = Response::html("<html>...");

// Generate a JSON response
$response = Response::json($myObject);

// Generate a Response that transfert a file to the client
$response = Response::file("/path/to/my/file.txt");

// Generate a Response that redirect the Client
$response = Response::redirect("/another/url");

// Try to make a Response of whatever is given to it
$response = Response::adapt($anyObject);

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

// Get raw content object
$content = $response->getContent();

$response->logSelf();

// Add/Overwrite headers into the Response
$response->withHeaders(["Content-Type" => "text/html"]);
// Get an associative headers array
$response->getHeaders();

// Display the response to the client
$response->display();
// Only display the content, skip the headers
$response->display(false);
```

## Advanced Request Usage (Curl !)

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
// $logger: A Logger can be given to log debug informations
// $timeout: If given, will given the CURL handler a max timeout
// $userAgent: The default userAgent can be overwritten too
// $supportRedirection: If `true`, fetch() will follow 3XX response and return the last response
$request->fetch(
    Logger $logger=null,
    int $timeout=null,
    string $userAgent='...',
    bool $supportRedirection=true
)
```

[< Back to summary](../home.md)