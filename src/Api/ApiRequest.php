<?php

namespace Bayfront\MultiCurl\Api;

class ApiRequest
{

    private string $id;
    private string $method;
    private string $path;
    private array $data;
    private array $headers;
    private bool $requires_authentication;

    /**
     * @param string $id
     * @param string $method
     * @param string $path
     * @param array $data
     * @param array $headers
     * @param bool $requires_authentication
     */
    public function __construct(string $id, string $method, string $path, array $data = [], array $headers = [], bool $requires_authentication = false)
    {
        $this->id = $id;
        $this->method = strtoupper($method);
        $this->path = ltrim($path, '/');
        $this->data = $data;
        $this->headers = $headers;
        $this->requires_authentication = $requires_authentication;
    }

    /**
     * Get unique request ID.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get HTTP request method.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get request path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get request data.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get request headers.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Does request require authentication?
     *
     * @return bool
     */
    public function requiresAuthentication(): bool
    {
        return $this->requires_authentication;
    }

}