# [Multi-cURL](../README.md) > API

The `Bayfront\MultiCurl\Api\InteractsWithApi` trait can be used to simplify making asynchronous requests to an API.

Example:

```php
class MyApiModel {

    use InteractsWithApi;
    
    public function __construct() 
    {
        $this
            ->setBaseUrl('https://api.example.com.com/v1')
            ->setHeaders($this::METHOD_POST, [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->setHeaders($this::METHOD_GET, [
                'Accept' => 'application/json'
            ])->setHeaders($this::METHOD_PATCH, [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->setHeaders($this::METHOD_DELETE, [
                'Accept' => 'application/json'
            ]);    
    }

}

$myApiModel = new MyApiModel();

$myApiModel->setAuthenticationHeaders([
    'X-Api-Key' => 'API_KEY'
]);

$myApiModel->addRequest(new ApiRequest('users', $myApiModel::METHOD_GET, '/users', [], [], true));
$myApiModel->addRequest(new ApiRequest('permissions', $myApiModel::METHOD_GET, '/permissions', [], [], true));

$myApiModel->execute();

$users = $myApiModel->getResponse('users');
$permissions = $myApiModel->getResponse('permissions');

$responses = [
    'users' => [
        'status' => $users->getStatus(),
        'headers' => $users->getHeaders(),
        'body' => $users->getBody()
    ],
    'permissions' => [
        'status' => $permissions->getStatus(),
        'headers' => $permissions->getHeaders(),
        'body' => $permissions->getBody()
    ]
];
```

Public methods include:

- [setBaseUrl](#setbaseurl)
- [getBaseUrl](#getbaseurl)
- [setAuthenticationHeaders](#setauthenticationheaders)
- [getAuthenticationHeaders](#getauthenticationheaders)
- [setHeaders](#setheaders)
- [getHeaders](#getheaders)
- [forgetHeaders](#forgetheaders)
- [addRequest](#addrequest)
- [execute](#execute)
- [getResponse](#getresponse)

## setBaseUrl

**Description:**

Set base URL.

**Parameters:**

- `$base_url` (string)

**Returns:**

- `$this`

## getBaseUrl

**Description:**

Get base URL.

**Parameters:**

- (none)

**Returns:**

- (string)

## setAuthenticationHeaders

**Description:**

Set authentication headers.

**Parameters:**

- `$headers` (array)

**Returns:**

- `$this`

## getAuthenticationHeaders

**Description:**

Get authentication headers.

**Parameters:**

- (none)

**Returns:**

- (array)

## setHeaders

**Description:**

Set headers for every request.

**Parameters:**

- `$method` (string): Valid `METHOD_*` constant
- `$headers` (array)

`METHOD_*` constants include:

- `METHOD_CONNECT`
- `METHOD_DELETE`
- `METHOD_GET`
- `METHOD_HEAD`
- `METHOD_OPTIONS`
- `METHOD_PATCH`
- `METHOD_POST`
- `METHOD_PUT`
- `METHOD_TRACE`

**Returns:**

- `$this`

## forgetHeaders

**Description:**

Remove headers for every request.

**Parameters:**

- `$method` (string): Valid `METHOD_*` constant (see above)
- `$header_keys` (array): Array of keys to remove

**Returns:**

- `$this`

## getHeaders

**Description:**

Get headers for every request.

**Parameters:**

- `$method` (string): Valid `METHOD_*` constant

**Returns:**

- (array)

## addRequest

**Description:**

Add API request.

**Parameters:**

- `$apiRequest` ([ApiRequest](apirequest.md))

**Returns:**

- `$this`

**Throws:**

- `Bayfront\MultiCurl\Exceptions\ApiException`

## execute

**Description:**

Execute the added requests.

This method must be called after all requests are added, and before [getResponse](#getresponse).

**Parameters:**

- (none)

**Returns:**

- `$this`

**Throws:**

- `Bayfront\MultiCurl\Exceptions\ApiException`

## getResponse

**Description:**

Get API response.

**Parameters:**

- `$id` (string): Unique request ID

**Returns:**

- ([ApiResponse](apiresponse.md))

**Throws:**

- `Bayfront\MultiCurl\Exceptions\ApiException`