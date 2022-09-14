<?php

namespace MondayCloneClient\Features;

use MondayCloneClient\Core\HttpService;

class PluginBootstrap
{
    const TENANT_ROLES = 'WPCS_TENANT_ROLES';
    const EXTERNAL_ID = 'WPCS_TENANT_EXTERNAL_ID';
    const API_V1_NAMESPACE = 'monday-client/v1';
    const PLUGIN_NAME = 'monday-client/index.php';
    const PLUGIN_VERSION = '2.0.0';

    private HttpService $httpService;

    public function __construct(HttpService $httpService)
    {
        $this->httpService = $httpService;

        add_action('wpcs_tenant_created', [$this, 'remove_access_to_plugins'], 10);
    }

    public function remove_access_to_plugins($external_id): void
    {
        $role = get_role('administrator');
        $plugins_capabilities = ['activate_plugins', 'delete_plugins', 'install_plugins', 'update_plugins', 'edit_plugins', 'upload_plugins'];
        foreach ($plugins_capabilities as $capability) {
            $role->remove_cap($capability);
        }

        update_option(self::EXTERNAL_ID, $external_id);

        $roles = $this->httpService->get('/user-role-plan/tenant?externalId=' . $external_id);
        update_option(PluginBootstrap::TENANT_ROLES, $roles);
    }
}
