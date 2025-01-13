<?php

namespace Bayfront\MultiCurl\ApiModel;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\MultiCurl\ApiModelException;
use Bayfront\MultiCurl\Async;
use Bayfront\MultiCurl\ClientException;

class ApiModel
{

    private string $base_url;

    public function __construct(string $base_url)
    {
        $this->base_url = $base_url;
    }

    /**
     * Get base URL.
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->base_url;
    }

    private array $authentication_headers = [];

    /**
     * Set authentication headers.
     *
     * @param array $headers
     * @return $this
     */
    public function setAuthenticationHeaders(array $headers): self
    {
        $this->authentication_headers = array_merge($this->authentication_headers, $headers);
        return $this;
    }

    private array $headers = [];

    /**
     * Set headers for every request.
     *
     * @param string $method (Valid METHOD_* constant)
     * @param array $headers
     * @return $this
     */
    public function setHeaders(string $method, array $headers): self
    {
        $this->headers[$method] = array_merge(Arr::get($this->headers, $method, []), $headers);
        return $this;
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

    /**
     * Perform requests and return an array of responses.
     *
     * @param array $requests (Key = Response ID, Value = ApiModelRequest)
     * @return array (Key = Response ID, Value = ApiModelResponse)
     * @throws ApiModelException
     * @throws ClientException
     */
    public function request(array $requests): array
    {

        $async = new Async($this->base_url);

        foreach ($requests as $id => $request) {

            if (!$request instanceof ApiModelRequest) {
                throw new ApiModelException('Unable to perform request (' . $id . '): Value must be instance of ApiModelRequest');
            }

            $async->use($id);

            // Headers

            if ($request->requiresAuthentication()) {
                $async->setHeaders($this->authentication_headers);
            }

            $async->setHeaders($request->getHeaders());

            $request_method = $request->getMethod();

            if ($request_method === self::METHOD_CONNECT) {
                $async->connect($request->getPath(), $request->getData());
            } else if ($request_method === self::METHOD_DELETE) {
                $async->delete($request->getPath(), $request->getData());
            } else if ($request_method === self::METHOD_GET) {
                $async->get($request->getPath(), $request->getData());
            } else if ($request_method === self::METHOD_HEAD) {
                $async->head($request->getPath(), $request->getData());
            } else if ($request_method === self::METHOD_OPTIONS) {
                $async->options($request->getPath(), $request->getData());
            } else if ($request_method === self::METHOD_PATCH) {
                $async->patch($request->getPath(), $request->getData());
            } else if ($request_method === self::METHOD_POST) {
                $async->post($request->getPath(), $request->getData());
            } else if ($request_method === self::METHOD_PUT) {
                $async->put($request->getPath(), $request->getData());
            } else if ($request_method === self::METHOD_TRACE) {
                $async->trace($request->getPath(), $request->getData());
            } else {
                throw new ApiModelException('Unable to perform request(' . $id . '): Invalid request method (' . $request_method . ')');
            }

        }

        $async->execute();

        $request_ids = array_keys($requests);
        $return = [];

        foreach ($request_ids as $id) {

            $async->use($id);

            $return[$id] = new ApiModelResponse($async->getStatusCode(), $async->getBody(true));

        }

        return $return;

    }

}