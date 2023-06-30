<?php

namespace WaaSHost\Api;

use WaaSHost\Core\HttpService;
use WaaSHost\Core\WPCSService;
use WaaSHost\Features\AdminNotices;
use WaaSHost\Features\PluginBootstrap;

class RolesController
{
    private WPCSService $wpcsService;

    public function __construct(WPCSService $wpcsService)
    {
        $this->wpcsService = $wpcsService;

        add_action('rest_api_init', [$this, 'register_rest_routes']);
        add_action('admin_post_wpcs_refresh_roles', [$this, 'admin_post_update_tenant_roles_list']);
    }

    public function register_rest_routes()
    {
        register_rest_route(PluginBootstrap::API_V1_NAMESPACE, '/user-role-plan/update', [
            'methods' => 'GET',
            'permission_callback' => '__return_true',
            'callback' => [$this, 'update_tenant_roles_list_callback'],
        ]);
    }

    public function admin_post_update_tenant_roles_list()
    {
        try {
            $this->update_tenant_roles_list();
        } catch (\Exception $error) {
            // Set a transient notice message
            set_transient(AdminNotices::REFRESH_ROLES_FAILED_NOTICE, 'Roles could not be refreshed. Please check that the state of the version labelled "Production" is "Running", has the WaaS-Client installed and has actual roles.', 5);
        }

        wp_safe_redirect(wp_get_referer());
    }

    public function update_tenant_roles_list()
    {
        $wpcs_production_version = $this->wpcsService->get_production_version();

        $wpcs_production_version_domain_name = $wpcs_production_version->domain;
        if (strpos($wpcs_production_version_domain_name, 'http') !== 0) {
            $wpcs_production_version_domain_name = 'https://' . $wpcs_production_version_domain_name;
        }

        $http_service = new HttpService($wpcs_production_version_domain_name);

        $response = $http_service->get('/wp-content/plugins/waas-client-data/roles.json');

        update_option(PluginBootstrap::ROLES_WP_OPTION, $response);
    }

    public function update_tenant_roles_list_callback(): \WP_REST_Response
    {
        try {
            $this->update_tenant_roles_list();

            return new \WP_REST_Response(true, 200);
        } catch (\Exception $error) {
            return new \WP_REST_Response(false, 404);
        }
    }
}
