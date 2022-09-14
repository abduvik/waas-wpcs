<?php

namespace MondayCloneHost\Api;

use MondayCloneHost\Core\HttpService;
use MondayCloneHost\Core\WPCSService;
use MondayCloneHost\Core\WPCSTenant;
use MondayCloneHost\Features\PluginBootstrap;
use PhpParser\Error;

class RolesController
{
    private WPCSService $wpcsService;

    public function __construct(WPCSService $wpcsService)
    {
        $this->wpcsService = $wpcsService;

        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }

    public function register_rest_routes()
    {


        register_rest_route(PluginBootstrap::API_V1_NAMESPACE, '/user-role-plan/update', [
            'methods' => 'GET',
            'permission_callback' => '__return_true',
            'callback' => [$this, 'update_user_roles_list'],
        ]);

        register_rest_route(PluginBootstrap::API_V1_NAMESPACE, '/user-role-plan/tenant', [
            'methods' => 'GET',
            'permission_callback' => '__return_true',
            'callback' => [$this, 'get_tenant_user_roles'],
        ]);
    }

    public function update_user_roles_list(): \WP_REST_Response
    {
        try {
            $wpcs_production_version = $this->wpcsService->get_production_version();

            $wpcs_production_version_domain_name = $wpcs_production_version->domain;
            if (strpos($wpcs_production_version_domain_name, 'http') !== 0) {
                $wpcs_production_version_domain_name = 'https://' . $wpcs_production_version_domain_name;
            }

            $http_service = new HttpService($wpcs_production_version_domain_name);

            $response = $http_service->get('/wp-content/plugins/monday-client-data/roles.json');

            update_option(PluginBootstrap::ROLES_WP_OPTION, $response);

            return new \WP_REST_Response(true, 200);

        } catch (Error $error) {
            return new \WP_REST_Response(false, 404);
        }
    }

    public function get_tenant_user_roles(\WP_REST_Request $request): \WP_REST_Response
    {
        $externalId = sanitize_text_field($request->get_param('externalId'));
        $tenant = WPCSTenant::from_wpcs_external_id($externalId);
        $subscription_id = $tenant->get_subscription_id();
        $roles = get_post_meta($subscription_id, WPCSTenant::WPCS_SUBSCRIPTION_USER_ROLES, true);
        return new \WP_REST_Response($roles, 200);
    }
}