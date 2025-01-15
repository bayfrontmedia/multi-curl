<?php

namespace Bayfront\MultiCurl;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\MultiCurl\Exceptions\ClientException;

class ClientParent
{

    private const MULTI_CURL_VERSION = '3.0.0';

    private string $base_url;

    /**
     * Constructor
     *
     * @param string $base_url
     */
    public function __construct(string $base_url = '')
    {
        $this->base_url = $base_url;
    }

    private const METHOD_CONNECT = 'CONNECT';
    private const  METHOD_DELETE = 'DELETE';
    private const  METHOD_GET = 'GET';
    private const  METHOD_HEAD = 'HEAD';
    private const  METHOD_OPTIONS = 'OPTIONS';
    private const  METHOD_PATCH = 'PATCH';
    private const  METHOD_POST = 'POST';
    private const  METHOD_PUT = 'PUT';
    private const  METHOD_TRACE = 'TRACE';

    /*
     * PHP does not support type declarations for "resource"
     * See: https://www.php.net/manual/en/language.types.declarations.php
     */

    protected mixed $current_handle;

    // cURL handles

    protected array $handles = [];

    /*
     * Keys include:
     *
     * headers
     * options
     * request_method
     * url
     */

    protected array $requests = [];

    /*
     * Keys include:
     *
     * response_headers
     * body
     * error
     * error_message
     * error_number
     * http_status_code
     */

    protected array $responses = [];

    /**
     * Sets options during execute().
     *
     * @param string $id
     * @param $handle
     * @return void
     */
    protected function curlSetOpt(string $id, $handle): void
    {

        if (isset($this->requests[$id]['options'])) {
            curl_setopt_array($handle, $this->requests[$id]['options']);
        }

        // Headers

        if (isset($this->requests[$id]['headers'])) {

            $headers = [];

            foreach ($this->requests[$id]['headers'] as $k => $v) {
                $headers[] = $k . ': ' . $v;
            }

            curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);

        }

    }

    /**
     * Extracts headers and body from a response which contains headers.
     *
     * @param string $id
     * @param $handle
     * @param $response
     * @return void
     */
    protected function curlProcessResponse(string $id, $handle, $response): void
    {

        // Get headers

        $header_size = curl_getinfo($handle, CURLINFO_HEADER_SIZE);

        $headers = explode("\n", substr($response, 0, $header_size));

        foreach ($headers as $header) {

            if (str_contains($header, ': ')) {

                $exp = explode(': ', $header, 2);

                if (isset($exp[1])) {

                    $this->responses[$id]['response_headers'][trim($exp[0])] = trim($exp[1]);

                }

            }

        }

        // Get body

        $this->responses[$id]['body'] = substr($response, $header_size);

    }

    /**
     * Saves information from a cURL response.
     *
     * @param string $id
     * @param $handle
     */
    protected function curlSetResponseInfo(string $id, $handle): void
    {

        $this->responses[$id]['error_number'] = curl_errno($handle);
        $this->responses[$id]['error'] = !($this->responses[$id]['error_number'] === 0);
        $this->responses[$id]['error_message'] = curl_error($handle);
        $this->responses[$id]['http_status_code'] = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));

    }

    /**
     * Returns the cURL handle.
     *
     * @returns resource
     * @throws ClientException
     */
    public function getHandle()
    {
        if (!isset($this->handles[$this->current_handle])) {
            throw new ClientException('Unable to get handle: handle does not exist');
        }

        return $this->handles[$this->current_handle];
    }

    /**
     * Resets all request settings.
     *
     * @return self
     */
    public function reset(): self
    {
        $this->requests = [];
        return $this;
    }

    /*
     * ############################################################
     * Request settings
     * ############################################################
     */

    /**
     * Sets default options for a cURL session.
     *
     * @return void
     */
    protected function setDefaultOptions(): void
    {

        $this->setOptions([
            CURLOPT_HEADER => true, // Return headers
            CURLOPT_RETURNTRANSFER => true, // Return as string
            CURLOPT_CONNECTTIMEOUT => 60, // Connect timeout
            CURLOPT_TIMEOUT => 60, // Execute timeout
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_USERAGENT => 'multi-curl/' . self::MULTI_CURL_VERSION . ' +https://github.com/bayfrontmedia/multi-curl'
        ]);

    }

    /**
     * Sets an array of options for the cURL session.
     *
     * @param array $options
     * @return self
     */
    public function setOptions(array $options): self
    {

        foreach ($options as $k => $v) {
            $this->requests[$this->current_handle]['options'][$k] = $v;
        }

        return $this;

    }

    /**
     * Sets an array of headers for the cURL session.
     *
     * @param array $headers
     * @return self
     */
    public function setHeaders(array $headers): self
    {

        foreach ($headers as $k => $v) {
            $this->requests[$this->current_handle]['headers'][$k] = $v;
        }

        return $this;

    }

    /**
     * Sets token authorization header for the cURL session using a given token.
     *
     * @param string $token
     * @return self
     */
    public function setToken(string $token): self
    {
        return $this->setHeaders([
            'Authorization' => 'Bearer ' . $token
        ]);
    }

    /*
     * ############################################################
     * Request
     * ############################################################
     */

    /**
     * Ensures proper formatting of the request URL.
     *
     * @param string $url
     *
     * @return string
     */
    protected function getRequestUrl(string $url): string
    {

        if ($this->base_url == '') {
            return trim(ltrim($url, '/'));
        }

        return trim(rtrim($this->base_url, '/') . '/' . ltrim($url, '/'));

    }

    /**
     * Sets the required options for specific request methods.
     *
     * If $json_encode is true and no Content-Type header has been set, a Content-Type header of "application/json"
     * will be added
     *
     * @param string $request_method
     * @param string $url
     * @param array $data
     * @param bool $json_encode
     * @return self
     */
    private function createRequest(string $request_method, string $url, array $data, bool $json_encode): self
    {

        $url = $this->getRequestUrl($url);

        if (!empty($data)) {

            if (true === $json_encode) {

                if (!isset($this->requests[$this->current_handle]['headers']['Content-Type'])) {

                    $this->setHeaders([
                        'Content-Type' => 'application/json'
                    ]);

                }

                $data = json_encode($data);

            }

            $this->setOptions([
                CURLOPT_POSTFIELDS => $data
            ]);

        }

        switch ($request_method) {

            case self::METHOD_CONNECT:

                $this->setOptions([
                    CURLOPT_URL => $url,
                    CURLOPT_CUSTOMREQUEST => self::METHOD_CONNECT
                ]);

                break;

            case self::METHOD_DELETE:

                $this->setOptions([
                    CURLOPT_URL => $url,
                    CURLOPT_CUSTOMREQUEST => self::METHOD_DELETE
                ]);

                break;

            case self::METHOD_HEAD:

                $this->setOptions([
                    CURLOPT_URL => $url,
                    CURLOPT_CUSTOMREQUEST => self::METHOD_HEAD
                ]);

                break;

            case self::METHOD_OPTIONS:

                $this->setOptions([
                    CURLOPT_URL => $url,
                    CURLOPT_CUSTOMREQUEST => self::METHOD_OPTIONS
                ]);

                break;

            case self::METHOD_PATCH:

                $this->setOptions([
                    CURLOPT_URL => $url,
                    CURLOPT_CUSTOMREQUEST => self::METHOD_PATCH
                ]);

                break;

            case self::METHOD_POST:

                $this->setOptions([
                    CURLOPT_URL => $url,
                    CURLOPT_POST => true
                ]);

                break;

            case self::METHOD_PUT:

                $this->setOptions([
                    CURLOPT_URL => $url,
                    CURLOPT_CUSTOMREQUEST => self::METHOD_PUT
                ]);

                break;

            case self::METHOD_TRACE:

                $this->setOptions([
                    CURLOPT_URL => $url,
                    CURLOPT_CUSTOMREQUEST => self::METHOD_TRACE
                ]);

                break;

        }

        $this->requests[$this->current_handle]['request_method'] = $request_method;

        $this->requests[$this->current_handle]['url'] = $url;

        return $this;

    }

    /**
     * Creates a GET request, including optional data sent as query parameters.
     *
     * @param string $url
     * @param array $data
     * @return self
     */
    public function get(string $url, array $data = []): self
    {

        $url = $this->getRequestUrl($url);

        if (!empty($data)) {

            $data = http_build_query($data);

            $url = $url . '?' . $data;

        }

        $this->setOptions([
            CURLOPT_URL => $url,
            CURLOPT_HTTPGET => true
        ]);

        $this->requests[$this->current_handle]['request_method'] = self::METHOD_GET;

        $this->requests[$this->current_handle]['url'] = $url;

        return $this;

    }

    /**
     * Creates a CONNECT request, including optional data.
     *
     * @param string $url
     * @param array $data
     * @param bool $json_encode (json_encode the $data array and set the Content-Type header as application/json, if
     *     not already defined)
     * @return self
     */
    public function connect(string $url, array $data = [], bool $json_encode = false): self
    {
        return $this->createRequest(self::METHOD_CONNECT, $url, $data, $json_encode);
    }

    /**
     * Creates a DELETE request, including optional data.
     *
     * @param string $url
     * @param array $data
     * @param bool $json_encode (json_encode the $data array and set the Content-Type header as application/json, if
     *     not already defined)
     * @return self
     */
    public function delete(string $url, array $data = [], bool $json_encode = false): self
    {
        return $this->createRequest(self::METHOD_DELETE, $url, $data, $json_encode);
    }

    /**
     * Creates a HEAD request, including optional data.
     *
     * @param string $url
     * @param array $data
     * @param bool $json_encode (json_encode the $data array and set the Content-Type header as application/json, if
     *     not already defined)
     * @return self
     */
    public function head(string $url, array $data = [], bool $json_encode = false): self
    {
        return $this->createRequest(self::METHOD_HEAD, $url, $data, $json_encode);
    }

    /**
     * Creates an OPTIONS request, including optional data.
     *
     * @param string $url
     * @param array $data
     * @param bool $json_encode (json_encode the $data array and set the Content-Type header as application/json, if
     *     not already defined)
     * @return self
     */
    public function options(string $url, array $data = [], bool $json_encode = false): self
    {
        return $this->createRequest(self::METHOD_OPTIONS, $url, $data, $json_encode);
    }

    /**
     * Creates a PATCH request, including optional data.
     *
     * @param string $url
     * @param array $data
     * @param bool $json_encode (json_encode the $data array and set the Content-Type header as application/json, if
     *     not already defined)
     * @return self
     */
    public function patch(string $url, array $data = [], bool $json_encode = false): self
    {
        return $this->createRequest(self::METHOD_PATCH, $url, $data, $json_encode);
    }

    /**
     * Creates a POST request, including optional data.
     *
     * @param string $url
     * @param array $data
     * @param bool $json_encode (json_encode the $data array and set the Content-Type header as application/json, if
     *     not already defined)
     * @return self
     */
    public function post(string $url, array $data = [], bool $json_encode = false): self
    {
        return $this->createRequest(self::METHOD_POST, $url, $data, $json_encode);
    }

    /**
     * Creates a PUT request, including optional data.
     *
     * @param string $url
     * @param array $data
     * @param bool $json_encode (json_encode the $data array and set the Content-Type header as application/json, if
     *     not already defined)
     * @return self
     */
    public function put(string $url, array $data = [], bool $json_encode = false): self
    {
        return $this->createRequest(self::METHOD_PUT, $url, $data, $json_encode);
    }

    /**
     * Creates a TRACE request, including optional data.
     *
     * @param string $url
     * @param array $data
     * @param bool $json_encode (json_encode the $data array and set the Content-Type header as application/json, if
     *     not already defined)
     * @return self
     */
    public function trace(string $url, array $data = [], bool $json_encode = false): self
    {
        return $this->createRequest(self::METHOD_TRACE, $url, $data, $json_encode);
    }

    /*
     * ############################################################
     * Response
     * ############################################################
     */

    /**
     * Returns array of headers from the previous request.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        if (isset($this->responses[$this->current_handle]['response_headers'])) {
            return $this->responses[$this->current_handle]['response_headers'];
        }

        return [];

    }

    /**
     * Returns single header value from the previous request, with optional default value if not existing.
     *
     * @param string $header
     * @param mixed|null $default
     * @return mixed
     */
    public function getHeader(string $header, mixed $default = NULL): mixed
    {

        if (isset($this->responses[$this->current_handle]['response_headers'])) {
            return Arr::get($this->responses[$this->current_handle]['response_headers'], $header, $default);
        }

        return $default;

    }

    /**
     * Returns body of the previous request, with optional default value if not existing.
     *
     * @param bool $json_decode (Decode JSON contents to an array)
     * @param mixed|null $default
     * @return mixed
     */
    public function getBody(bool $json_decode = false, mixed $default = NULL): mixed
    {

        if (isset($this->responses[$this->current_handle]['body'])) {

            if (true === $json_decode) {
                return json_decode($this->responses[$this->current_handle]['body'], true);
            }

            return $this->responses[$this->current_handle]['body'];

        }

        return $default;

    }

    /**
     * Returns error number of the previous request, or zero if no error exists.
     *
     * @return int
     */
    public function getErrorNumber(): int
    {

        if (isset($this->responses[$this->current_handle]['error_number'])) {
            return $this->responses[$this->current_handle]['error_number'];
        }

        return 0;

    }

    /**
     * Is previous request an error.
     *
     * @return bool
     */
    public function isError(): bool
    {

        if (isset($this->responses[$this->current_handle]['error'])) {
            return $this->responses[$this->current_handle]['error'];
        }

        return false;

    }

    /**
     * Returns error message of the previous request, or an empty string if no error occurred.
     *
     * @return string
     */
    public function getErrorMessage(): string
    {

        if (isset($this->responses[$this->current_handle]['error_message'])) {
            return $this->responses[$this->current_handle]['error_message'];
        }

        return '';

    }

    /**
     * Returns status code of the previous request, or zero if not existing.
     *
     * @return int
     */
    public function getStatusCode(): int
    {

        if (isset($this->responses[$this->current_handle]['http_status_code'])) {
            return $this->responses[$this->current_handle]['http_status_code'];
        }

        return 0;

    }

    /**
     * Returns array of information about the previous request, a single option constant, or null if not existing.
     *
     * @param mixed|null $opt (Optional option constant)
     *
     * See: https://www.php.net/manual/en/function.curl-getinfo.php#refsect1-function.curl-getinfo-parameters
     *
     * @return mixed
     */
    public function getInfo(mixed $opt = NULL): mixed
    {

        if (isset($this->handles[$this->current_handle])) {

            if (NULL === $opt) {
                return curl_getinfo($this->handles[$this->current_handle]); // Return entire array
            }

            return curl_getinfo($this->handles[$this->current_handle], $opt); // Return single value

        }

        return NULL;

    }

    /**
     * Is status code informational.
     *
     * @return bool
     */
    public function isInformational(): bool
    {
        $status = $this->getStatusCode();
        return $status >= 100 && $status < 200;
    }

    /**
     * Is status code successful.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        $status = $this->getStatusCode();
        return $status >= 200 && $status < 300;
    }

    /**
     * Is status code a redirection.
     *
     * @return bool
     */
    public function isRedirection(): bool
    {
        $status = $this->getStatusCode();
        return $status >= 300 && $status < 400;
    }

    /**
     * Is status code a client error.
     *
     * @return bool
     */
    public function isClientError(): bool
    {
        $status = $this->getStatusCode();
        return $status >= 400 && $status < 500;
    }

    /**
     * Is status code a server error.
     *
     * @return bool
     */
    public function isServerError(): bool
    {
        $status = $this->getStatusCode();
        return $status >= 500 && $status < 600;
    }

    /**
     * Is status code OK (200).
     *
     * @return bool
     */
    public function isOk(): bool
    {
        return 200 === $this->getStatusCode();
    }

    /**
     * Is status code forbidden (403).
     *
     * @return bool
     */
    public function isForbidden(): bool
    {
        return 403 === $this->getStatusCode();
    }

    /**
     * Is status code not found (404).
     *
     * @return bool
     */
    public function isNotFound(): bool
    {
        return 404 === $this->getStatusCode();
    }

}