<?php

namespace Bayfront\MultiCurl\Api;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\MultiCurl\Async;
use Bayfront\MultiCurl\Exceptions\ClientException;
use Bayfront\MultiCurl\Exceptions\ApiException;

trait InteractsWithApi
{

    private static string $base_url = '';

    /**
     * Set base URL.
     *
     * @param string $base_url
     * @return $this
     */
    public function setBaseUrl(string $base_url): static
    {
        self::$base_url = $base_url;
        return $this;
    }

    /**
     * Get base URL.
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return self::$base_url;
    }

    private static array $authentication_headers = [];

    /**
     * Set authentication headers.
     *
     * @param array $headers
     * @return $this
     */
    public function setAuthenticationHeaders(array $headers): static
    {
        self::$authentication_headers = array_merge(self::$authentication_headers, $headers);
        return $this;
    }

    /**
     * Get authentication headers.
     *
     * @return array
     */
    public function getAuthenticationHeaders(): array
    {
        return self::$authentication_headers;
    }

    public const METHOD_CONNECT = 'CONNECT';
    public const  METHOD_DELETE = 'DELETE';
    public const  METHOD_GET = 'GET';
    public const  METHOD_HEAD = 'HEAD';
    public const  METHOD_OPTIONS = 'OPTIONS';
    public const  METHOD_PATCH = 'PATCH';
    public const  METHOD_POST = 'POST';
    public const  METHOD_PUT = 'PUT';
    public const  METHOD_TRACE = 'TRACE';

    private static array $headers = [];

    /**
     * Set headers for every request.
     *
     * @param string $method (Valid METHOD_* constant)
     * @param array $headers
     * @return $this
     */
    public function setHeaders(string $method, array $headers): static
    {
        self::$headers[$method] = array_merge(Arr::get(self::$headers, $method, []), $headers);
        return $this;
    }

    /**
     * Remove headers for every request.
     *
     * @param string $method (Valid METHOD_* constant)
     * @param array $header_keys (Array of keys to remove)
     * @return $this
     */
    public function forgetHeaders(string $method, array $header_keys): static
    {
        Arr::forget(self::$headers[$method], $header_keys);
        return $this;
    }

    /**
     * Get headers for every request.
     *
     * @param string $method (Valid METHOD_* constant)
     * @return array
     */
    public function getHeaders(string $method): array
    {
        return Arr::get(self::$headers, $method, []);
    }

    private ?Async $async = null;
    private array $request_ids = [];

    /**
     * Add API request.
     *
     * @param ApiRequest $apiRequest
     * @return $this
     * @throws ApiException
     */
    public function addRequest(ApiRequest $apiRequest): static
    {

        if ($this->async === null) {
            $this->responses = []; // Reset
            $this->async = new Async($this->getBaseUrl());
        }

        $this->async->create([$apiRequest->getId()]);

        try {
            $this->async->use($apiRequest->getId());
        } catch (ClientException) {
            throw new ApiException('Unable to add request (' . $apiRequest->getId() . '): Invalid request ID');
        }

        if ($apiRequest->requiresAuthentication()) {
            $this->async->setHeaders($this->getAuthenticationHeaders());
        }

        $this->async->setHeaders($apiRequest->getHeaders());

        $request_method = $apiRequest->getMethod();

        $this->async->setHeaders($this->getHeaders($request_method));

        if ($request_method === self::METHOD_CONNECT) {
            $this->async->connect($apiRequest->getPath(), $apiRequest->getData(), true);
        } else if ($request_method === self::METHOD_DELETE) {
            $this->async->delete($apiRequest->getPath(), $apiRequest->getData(), true);
        } else if ($request_method === self::METHOD_GET) {
            $this->async->get($apiRequest->getPath(), $apiRequest->getData());
        } else if ($request_method === self::METHOD_HEAD) {
            $this->async->head($apiRequest->getPath(), $apiRequest->getData(), true);
        } else if ($request_method === self::METHOD_OPTIONS) {
            $this->async->options($apiRequest->getPath(), $apiRequest->getData(), true);
        } else if ($request_method === self::METHOD_PATCH) {
            $this->async->patch($apiRequest->getPath(), $apiRequest->getData(), true);
        } else if ($request_method === self::METHOD_POST) {
            $this->async->post($apiRequest->getPath(), $apiRequest->getData(), true);
        } else if ($request_method === self::METHOD_PUT) {
            $this->async->put($apiRequest->getPath(), $apiRequest->getData(), true);
        } else if ($request_method === self::METHOD_TRACE) {
            $this->async->trace($apiRequest->getPath(), $apiRequest->getData(), true);
        } else {
            throw new ApiException('Unable to add request(' . $apiRequest->getId() . '): Invalid request method (' . $request_method . ')');
        }

        $this->request_ids[] = $apiRequest->getId();

        return $this;

    }

    private array $responses = [];

    /**
     * Execute the added requests.
     *
     * This method must be called after all requests are added, and before getResponse.
     *
     * @return $this
     * @throws ApiException
     */
    public function execute(): static
    {

        if (!$this->async instanceof Async) {
            return $this;
        }

        $this->async->execute();

        foreach ($this->request_ids as $id) {

            try {
                $this->async->use($id);
            } catch (ClientException) {
                throw new ApiException('Unable to execute: Invalid ID(s)');
            }

            $this->responses[$id] = [
                'status' => $this->async->getStatusCode(),
                'headers' => $this->async->getHeaders(),
                'body' => $this->async->getBody(true)
            ];

        }

        // Reset
        $this->async->close();
        $this->async = null;
        $this->request_ids = [];

        return $this;

    }

    /**
     * Get API response.
     *
     * @param string $id : Unique request ID
     * @return ApiResponse
     * @throws ApiException
     */
    public function getResponse(string $id): ApiResponse
    {

        if (!isset($this->responses[$id])) {
            throw new ApiException('Unable to get response (' . $id . '): Invalid ID');
        }

        return new ApiResponse(Arr::get($this->responses[$id], 'status', 0), Arr::get($this->responses[$id], 'headers', []), Arr::get($this->responses[$id], 'body'));

    }

}