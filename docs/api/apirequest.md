# [Multi-cURL](../README.md) > [API](README.md) > ApiRequest

The `Bayfront\MultiCurl\Api\ApiRequest` class is used to define a single request to be used with the [InteractsWithApi trait](README.md).

Its constructor requires:

- `$id` (string)
- `$method` (string)
- `$path` (string)
- `$data = []` (array)
- `$headers = []` (array)
- `$requires_authentication = false` (bool)

Public methods include:

- [getId](#getid)
- [getMethod](#getmethod)
- [getPath](#getpath)
- [getData](#getdata)
- [getHeaders](#getheaders)
- [requiresAuthentication](#requiresauthentication)

## getId

**Description:**

Get unique request ID.

**Parameters:**

- (none)

**Returns:**

- (string)

## getMethod

**Description:**

Get HTTP request method.

**Parameters:**

- (none)

**Returns:**

- (string)

## getPath

**Description:**

Get request path.

**Parameters:**

- (none)

**Returns:**

- (string)

## getData

**Description:**

Get request data.

**Parameters:**

- (none)

**Returns:**

- (array)

## getHeaders

**Description:**

Get request headers.

**Parameters:**

- (none)

**Returns:**

- (array)

## requiresAuthentication

**Description:**

Does request require authentication?

**Parameters:**

- (none)

**Returns:**

- (bool)