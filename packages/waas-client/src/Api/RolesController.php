<?php

namespace WaaSClient\Api;

use WaaSClient\Core\HttpService;
use WaaSClient\Features\PluginBootstrap;

class RolesController
{
    private HttpService $httpService;

    public function __construct(HttpService $httpService)
    {
        $this->httpService = $httpService;

        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }

    public function register_rest_routes()
    {
        register_rest_route(PluginBootstrap::API_V1_NAMESPACE, '/user-role-plan/fetch-updated-list', [
            'methods' => 'GET',
            'callback' => [$this, 'fetch_update_user_role_list'],
        ]);
    }

    public function fetch_update_user_role_list(): \WP_REST_Response
    {
        try {
            $externalId = get_option(PluginBootstrap::EXTERNAL_ID);
            $roles = $this->httpService->get('/user-role-plan/tenant?externalId=' . $externalId);
            update_option(PluginBootstrap::TENANT_ROLES, $roles);

            return new \WP_REST_Response(true, 200);
        } catch (\Exception $e) {
            return new \WP_REST_Response(false, 500);
        }
    }
}