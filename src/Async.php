<?php

namespace Bayfront\MultiCurl;

class Async extends ClientParent
{

    /*
     * PHP does not support type declarations for "resource"
     * See: https://www.php.net/manual/en/language.types.declarations.php
     */

    protected mixed $mh;

    /**
     * Constructor
     *
     * @param string $base_url
     *
     * @throws ClientException
     */

    public function __construct(string $base_url = '')
    {

        parent::__construct($base_url);

        $this->mh = curl_multi_init();

    }

    public function __destruct()
    {
        $this->close();
    }

    protected bool $has_closed = false;

    /**
     * Reset all settings and close the cURL handles
     *
     * NOTE: This method is called in the class destructor
     *
     * @return self
     */

    public function close(): self
    {

        if (false === $this->has_closed) {

            $this->reset(); // Reset all request settings

            // Reset all response settings

            $this->responses = [];

            // Close the handles

            foreach ($this->handles as $handle) {
                curl_close($handle);
            }

            $this->handles = [];

            curl_multi_close($this->mh);

            $this->has_closed = true;

        }

        return $this;

    }

    /**
     * Create cURL handles with identifiers
     *
     * cURL handles must be created before they can be used.
     *
     * @param array $ids
     *
     * @return self
     */

    public function create(array $ids): self
    {

        foreach ($ids as $id) {

            $this->handles[$id] = curl_init();

            $this->current_handle = $id;

            $this->_setDefaultOptions();

        }

        return $this;

    }

    /**
     * Sets current cURL handle
     *
     * Once the cURL handle has been created using create(),
     * it can be used by specifying the ID of the handle you wish to use.
     *
     * @param string $id
     *
     * @return self
     *
     * @throws ClientException
     */

    public function use(string $id): self
    {

        if (!isset($this->handles[$id])) {

            throw new ClientException('Unable to use client: id does not exist');

        }

        $this->current_handle = $id;

        return $this;

    }

    /**
     * Execute the given cURL session
     *
     * The response methods will only return results after the execute() method has been called.
     *
     * @return self
     */

    public function execute(): self
    {

        foreach ($this->handles as $id => $handle) {

            $this->_curlSetOpt($id, $handle);

            curl_multi_add_handle($this->mh, $handle);

        }

        do {

            curl_multi_exec($this->mh, $running);
            curl_multi_select($this->mh);

        } while ($running > 0);

        foreach ($this->handles as $id => $handle) {

            // cURL response

            if (isset($this->requests[$id]['options'][CURLOPT_HEADER]) && true === $this->requests[$id]['options'][CURLOPT_HEADER]) { // If headers are part of the response

                $response = curl_multi_getcontent($handle);

                $this->_curlProcessResponse($id, $handle, $response);

            } else {

                $this->responses[$id]['body'] = curl_multi_getcontent($handle);

            }

            $this->_curlSetResponseInfo($id, $handle);

            curl_multi_remove_handle($this->mh, $handle);

        }

        $this->reset(); // Reset all request settings

        return $this;

    }

}