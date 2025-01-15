# [Multi-cURL](../README.md) > [API](README.md) > ApiResponse

The `Bayfront\MultiCurl\Api\ApiResponse` class is used to define the response of a single request from the [InteractsWithApi trait](README.md).

Its constructor requires:

- `$status` (int)
- `$headers = []` (array)
- `$body = []` (array)

Public methods include:

- [getStatus](#getstatus)
- [getHeaders](#getheaders)
- [getBody](#getbody)

## getStatus

**Description:**

Get response HTTP status code.

**Parameters:**

- (none)

**Returns:**

- (int)

## getHeaders

**Description:**

Get response headers.

**Parameters:**

- (none)

**Returns:**

- (array)

## getBody

**Description:**

Get response body.

**Parameters:**

- (none)

**Returns:**

- (array)