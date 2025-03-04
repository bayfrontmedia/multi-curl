<?php

namespace Bayfront\MultiCurl\Api;

class ApiResponse
{

    private int $status;
    private array $headers;
    private ?array $body;

    public function __construct(int $status, array $headers, ?array $body)
    {
        $this->status = $status;
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * Get response HTTP status code.
     *
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Get response headers.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get response body.
     *
     * @return array|null
     */
    public function getBody(): ?array
    {
        return $this->body;
    }

}