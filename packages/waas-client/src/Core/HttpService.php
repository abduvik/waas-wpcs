<?php

namespace WaaSClient\Core;

use Exception;

class HttpService
{
    private string $base_uri;

    public function __construct($base_uri)
    {
        $this->base_uri = $base_uri;
    }

    /**
     * @throws Exception
     */
    public function get($uri)
    {
        $response = wp_remote_get($this->base_uri . $uri);

        if (is_wp_error($response)) {
            throw new Exception("Failed");
        }

        return json_decode($response['body']);
    }
}
