## Multi cURL

A simple HTTP client to make single or asynchronous requests utilizing the cURL library.

- [License](#license)
- [Author](#author)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)

## License

This project is open source and available under the [MIT License](LICENSE).

## Author

<img src="https://cdn1.onbayfront.com/bfm/brand/bfm-logo.svg" alt="Bayfront Media" width="250" />

- [Bayfront Media homepage](https://www.bayfrontmedia.com?utm_source=github&amp;utm_medium=direct)
- [Bayfront Media GitHub](https://github.com/bayfrontmedia)

## Requirements

- PHP `^8.0`
- cURL PHP extension
- JSON PHP extension

## Installation

```
composer require bayfrontmedia/multi-curl
```

## Usage

### Single HTTP request

```
use Bayfront\MultiCurl\Client;

$client = new Client();
```

A base URL can be set so that future request-related methods do not have to specify the full endpoint. For example:

```
use Bayfront\MultiCurl\Client;

$client = new Client('https://jsonplaceholder.typicode.com');

$response = $client->get('posts/1'); // Endpoint from the base URL

echo $response->getBody();
```

The cURL handle will be created automatically and be ready to use.

### Asynchronous HTTP requests

Multiple HTTP requests can be made simultaneously instead of one after the other, thereby limiting the completion time to the duration of the single slowest request instead of the sum of all requests combined.

```
use Bayfront\MultiCurl\Async;

$async = new Async();
```

A base URL can be set so that future request-related methods do not have to specify the full endpoint. For example:

```
use Bayfront\MultiCurl\Async;
use Bayfront\MultiCurl\ClientException;

$async = new Async('https://jsonplaceholder.typicode.com');

$ids = [
    'posts',
    'comments'
];

$async->create($ids);

try {

    $async
        ->use('posts')->get('posts/1')
        ->use('comments')->get('comments/1');

} catch (ClientException $e) {
    die($e->getMessage());
}

$async->execute();

foreach ($ids as $id) {

    try {

        echo $async->use($id)->getBody();

    } catch (ClientException $e) {
        die($e->getMessage());
    }

}
```

cURL handles must be made explicitly using the `create()` method before using.

### Public methods

**Async class only**

- [create](#create)
- [use](#use)
- [execute](#execute)

Once a cURL handle has been created, the following methods can be used:

- [getHandle](#gethandle)
- [reset](#reset)
- [close](#close)
- [setOptions](#setoptions)
- [setHeaders](#setheaders)
- [setToken](#settoken)

**Request**   
- [download](#download)
- [get](#get)
- [connect](#connect)
- [delete](#delete)
- [head](#head)
- [options](#options)
- [patch](#patch)
- [post](#post)
- [put](#put)
- [trace](#trace)

**Response**

- [getHeaders](#getheaders)
- [getHeader](#getheader)
- [getBody](#getbody)
- [getErrorNumber](#geterrornumber)
- [isError](#iserror)
- [getErrorMessage](#geterrormessage)
- [getStatusCode](#getstatuscode)
- [getInfo](#getinfo)
- [isInformational](#isinformational)
- [isSuccessful](#issuccessful)
- [isRedirection](#isredirection)
- [isClientError](#isclienterror)
- [isServerError](#isservererror)
- [isOk](#isok)
- [isForbidden](#isforbidden)
- [isNotFound](#isnotfound)

<hr />

### create

**Description:**

(Only available in `Async` class)

Create cURL handles with identifiers.

cURL handles must be created before they can be used.

**Parameters:**

- `$ids` (array)

**Returns:**

- (self)

**Example:**

```
use Bayfront\MultiCurl\Async;

$async = new Async('https://jsonplaceholder.typicode.com');

$ids = [
    'posts',
    'comments'
];

$async->create($ids);
```

<hr />

### use

**Description:**

(Only available in `Async` class)

Sets current cURL handle.

Once the cURL handle has been created using `create()`, it can be used by specifying the ID of the handle you wish to use.

A `Bayfront\MultiCurl\ClientException` exception will be thrown if the ID does not exist.

**Parameters:**

- `$id` (string)

**Returns:**

- (self)

**Throws:**

- `Bayfront\MultiCurl\ClientException`

**Example:**

```
use Bayfront\MultiCurl\Async;
use Bayfront\MultiCurl\ClientException;

$async = new Async('https://jsonplaceholder.typicode.com');

$ids = [
    'posts',
    'comments'
];

$async->create($ids);

try {

    $async
        ->use('posts')->get('posts/1')
        ->use('comments')->get('comments/1');

} catch (ClientException $e) {
    die($e->getMessage());
}

$async->execute();
```

<hr />

### execute

**Description:**

(Only available in `Async` class)

Execute the given cURL session.

The response methods will only return results after the `execute()` method has been called.

**Parameters:**

- None

**Returns:**

- (self)

**Example:**

See the above example for [use()](#use).

<hr />

### getHandle

**Description:**

Returns the cURL handle.

A `Bayfront\MultiCurl\ClientException` exception will be thrown if the handle does not exist.

**Parameters:**

- None

**Returns:**

- (resource): cURL handle

**Throws:**

- `Bayfront\MultiCurl\ClientException`

<hr />

### reset

**Description:**

Resets all request settings.

**Parameters:**

- None

**Returns:**

- (self)

<hr />

### close

**Description:**

Reset all settings and close the cURL handle(s).

**NOTE:** This method is called in the class destructor.

**Parameters:**

- None

**Returns:**

- (self)

<hr />

### setOptions

**Description:**

Sets an array of options for the cURL session.

**Parameters:**

- `$options` (array)

**Returns:**

- (self)

**Example:**

```
$client->setOptions([
    CURLOPT_HEADER => false
]);
```

<hr />

### setHeaders

**Description:**

Sets an array of headers for the cURL session.

**Parameters:**

- `$headers` (array)

**Returns:**

- (self)

**Example:**

```
$client->setHeaders([
    'Content-Type' => 'application/json; charset=UTF-8'
]);
```

<hr />

### setToken

**Description:**

Sets token authorization header using a given token.

**Parameters:**

- `$token` (string)

**Returns:**

- (self)

<hr />

### download

**Description:**

(Only available in `Client` class)

Initiates file download in the browser.

**Parameters:**

- `$url` (string)
- `$memory_limit = 128` (int): Memory limit (in MB)

**Returns:**

- (void)

**Example:**

```
$client = new Client();
$client->download('https://www.example.com/image.jpg');
```

<hr />

### get

**Description:**

Executes `GET` request, including optional data sent as query parameters.

**Parameters:**

- `$url` (string)
- `$data = []` (array)

**Returns:**

- (self)

**Example:**

```
$client = new Client('https://jsonplaceholder.typicode.com');
$response = $client->get('posts/1');
```

<hr />

### connect

**Description:**

Executes `CONNECT` request, including optional data.

**Parameters:**

- `$url` (string)
- `$data = []` (array)
- `$json_encode = false` (bool): json_encode the `$data` array and set the `Content-Type` header as `application/json`, if not already defined

**Returns:**

- (self)

<hr />

### delete

**Description:**

Executes `DELETE` request, including optional data.

**Parameters:**

- `$url` (string)
- `$data = []` (array)
- `$json_encode = false` (bool): json_encode the `$data` array and set the `Content-Type` header as `application/json`, if not already defined

**Returns:**

- (self)

<hr />

### head

**Description:**

Executes `HEAD` request, including optional data.

**Parameters:**

- `$url` (string)
- `$data = []` (array)
- `$json_encode = false` (bool): json_encode the `$data` array and set the `Content-Type` header as `application/json`, if not already defined

**Returns:**

- (self)

<hr />

### options

**Description:**

Executes `OPTIONS` request, including optional data.

**Parameters:**

- `$url` (string)
- `$data = []` (array)
- `$json_encode = false` (bool): json_encode the `$data` array and set the `Content-Type` header as `application/json`, if not already defined

**Returns:**

- (self)

<hr />

### patch

**Description:**

Executes `PATCH` request, including optional data.

**Parameters:**

- `$url` (string)
- `$data = []` (array)
- `$json_encode = false` (bool): json_encode the `$data` array and set the `Content-Type` header as `application/json`, if not already defined

**Returns:**

- (self)

<hr />

### post

**Description:**

Executes `POST` request, including optional data.

**Parameters:**

- `$url` (string)
- `$data = []` (array)
- `$json_encode = false` (bool): json_encode the `$data` array and set the `Content-Type` header as `application/json`, if not already defined

**Returns:**

- (self)

<hr />

### put

**Description:**

Executes `PUT` request, including optional data.

**Parameters:**

- `$url` (string)
- `$data = []` (array)
- `$json_encode = false` (bool): json_encode the `$data` array and set the `Content-Type` header as `application/json`, if not already defined

**Returns:**

- (self)

<hr />

### trace

**Description:**

Executes `TRACE` request, including optional data.

**Parameters:**

- `$url` (string)
- `$data = []` (array)
- `$json_encode = false` (bool): json_encode the `$data` array and set the `Content-Type` header as `application/json`, if not already defined

**Returns:**

- (self)

<hr />

### getHeaders

**Description:**

Returns array of headers from the previous request.

**Parameters:**

- None

**Returns:**

- (array)

**Example:**

```
$client = new Client('https://jsonplaceholder.typicode.com');
$response = $client->get('posts/1');

print_r($response->getHeaders());
```

<hr />

### getHeader

**Description:**

Returns single header value from the previous request, with optional default value if not existing.

**Parameters:**

- `$header` (string)
- `$default = NULL` (mixed)

**Returns:**

- (mixed)

**Example:**

```
$client = new Client('https://jsonplaceholder.typicode.com');
$response = $client->get('posts/1');

echo $response->getHeader('Date');
```

<hr />

### getBody

**Description:**

Returns body of the previous request, with optional default value if not existing.

**Parameters:**

- `$json_decode = false` (bool): Decode JSON contents to an array
- `$default = NULL` (mixed)

**Returns:**

- (mixed)

**Example:**

```
$client = new Client('https://jsonplaceholder.typicode.com');
$response = $client->get('posts/1');

echo $response->getBody();
```

<hr />

### getErrorNumber

**Description:**

Returns error number of the previous request, or zero if no error exists.

**Parameters:**

- None

**Returns:**

- (int)

**Example:**

```
$client = new Client('https://jsonplaceholder.typicode.com');
$response = $client->get('posts/1');

echo $response->getErrorNumber();
```

<hr />

### isError

**Description:**

Is previous request an error.

**Parameters:**

- None

**Returns:**

- (bool)

<hr />

### getErrorMessage

**Description:**

Returns error message of the previous request, or an empty string if no error occurred.

**Parameters:**

- None

**Returns:**

- (string)

<hr />

### getStatusCode

**Description:**

Returns status code of the previous request, or zero if not existing.

**Parameters:**

- None

**Returns:**

- (int)

<hr />

### getInfo

**Description:**

Returns array of information about the previous request, a single option constant, or null if not existing.

**Parameters:**

- `$opt = NULL` (mixed): Optional option constant

For more information, see: [curl_getinfo](https://www.php.net/manual/en/function.curl-getinfo.php#refsect1-function.curl-getinfo-parameters)

**Returns:**

- (mixed)

<hr />

### isInformational

**Description:**

Is status code informational.

**Parameters:**

- None

**Returns:**

- (bool)

<hr />

### isSuccessful

**Description:**

Is status code successful.

**Parameters:**

- None

**Returns:**

- (bool)

<hr />

### isRedirection

**Description:**

Is status code a redirection.

**Parameters:**

- None

**Returns:**

- (bool)

<hr />

### isClientError

**Description:**

Is status code a client error.

**Parameters:**

- None

**Returns:**

- (bool)

<hr />

### isServerError

**Description:**

Is status code a server error.

**Parameters:**

- None

**Returns:**

- (bool)

<hr />

### isOk

**Description:**

Is status code OK (200).

**Parameters:**

- None

**Returns:**

- (bool)

<hr />

### isForbidden

**Description:**

Is status code forbidden (403).

**Parameters:**

- None

**Returns:**

- (bool)

<hr />

### isNotFound

**Description:**

Is status code not found (404).

**Parameters:**

- None

**Returns:**

- (bool)