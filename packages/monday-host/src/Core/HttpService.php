<?php

namespace MondayCloneHost\Core;

use Exception;

class HttpService
{
    private string $auth_keys;
    private string $base_uri;

    public function __construct(string $base_uri, string $auth_keys = '')
    {
        $this->auth_keys = $auth_keys;
        $this->base_uri = $base_uri;
    }

    /**
     * @throws Exception
     */
    public function get($uri)
    {
        $response = wp_remote_get($this->base_uri . $uri, [
            'headers' => $this->get_api_header()
        ]);

        if (is_wp_error($response)) {
            throw new Exception("Failed");
        }

        return json_decode($response['body']);
    }

    private function get_api_header(): array
    {
        if ($this->auth_keys === '') {
            return [
                'Content-Type' => 'application/json',
            ];
        }

        return [
            'Content-Type' => 'application/json',
            'Authorization' => "Basic " . base64_encode($this->auth_keys),
        ];
    }

    /**
     * @throws Exception
     */
    public function post($uri, $data)
    {
        $response = wp_remote_post($this->base_uri . $uri, [
            'method' => 'POST',
            'headers' => $this->get_api_header(),
            'body' => json_encode($data)
        ]);

        if (is_wp_error($response)) {
            throw new Exception("Failed");
        }

        return json_decode($response['body']);
    }

    /**
     * @throws Exception
     */
    public function put($uri, $data)
    {
        $response = wp_remote_post($this->base_uri . $uri, [
            'method' => 'PUT',
            'headers' => $this->get_api_header(),
            'body' => json_encode($data)
        ]);

        if (is_wp_error($response)) {
            throw new Exception("Failed");
        }

        return json_decode($response['body']);
    }

    /**
     * @throws Exception
     */
    public function delete($uri)
    {
        $response = wp_remote_get($this->base_uri . $uri, [
            'method' => 'DELETE',
            'headers' => $this->get_api_header(),
        ]);

        if (is_wp_error($response)) {
            throw new Exception("Failed");
        }

        return json_decode($response['body']);
    }
}