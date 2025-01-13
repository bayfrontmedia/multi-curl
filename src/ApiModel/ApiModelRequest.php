<?php

namespace Bayfront\MultiCurl\ApiModel;

class ApiModelRequest
{

    private string $method;
    private string $path;
    private array $data;
    private array $headers;
    private bool $requires_authentication;

    public function __construct(string $method, string $path, array $data = [], array $headers = [], bool $requires_authentication = false)
    {
        $this->method = strtoupper($method);
        $this->path = ltrim($path, '/');
        $this->data = $data;
        $this->headers = $headers;
        $this->requires_authentication = $requires_authentication;
    }

    /**
     * Get request method.
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