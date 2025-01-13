<?php

namespace Bayfront\MultiCurl\ApiModel;

class ApiModelResponse
{

    private int $status;
    private array $response;

    public function __construct(int $status, array $response)
    {
        $this->status = $status;
        $this->response = $response;
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
     * Get response body.
     *
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }

}