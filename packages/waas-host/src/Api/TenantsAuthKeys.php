<?php

namespace WaaSHost\Api;

use Exception;
use WaaSHost\Core\WPCSTenant;
use WaaSHost\Features\PluginBootstrap;
use WP_Error;
use WP_REST_Request;

class TenantsAuthKeys
{
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }

    public function register_rest_routes()
    {
        register_rest_route(PluginBootstrap::API_V1_NAMESPACE, '/tenant/public_keys', array(
            'methods' => 'GET',
            'permission_callback' => '__return_true',
            'callback' => [$this, 'get_tenant_public_key'],
        ));
    }

    public function get_tenant_public_key(WP_REST_Request $request)
    {
        $external_id = $request->get_param('external_id');

        try {
            $tenant = WPCSTenant::from_wpcs_external_id($external_id);
            $tenant_auth_keys = $tenant->get_auth_keys();

            return [
                'public_key' => $tenant_auth_keys['public_key']
            ];

        } catch (Exception $e) {
            return new WP_Error('not_found');
        }
    }
}
