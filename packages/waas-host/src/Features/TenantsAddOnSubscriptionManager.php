<?php

namespace WaaSHost\Features;

use WaaSHost\Core\WPCSTenant;
use WaaSHost\Core\WPCSService;

class TenantsAddOnSubscriptionManager
{
    private WPCSService $wpcsService;
    public function __construct(WPCSService $wpcsService)
    {
        $this->wpcsService = $wpcsService;
        add_action('wpcs_tenant_roles_changed', [$this, 'update_tenant_roles'], 10, 2);
    }

    public function update_tenant_roles($subscription_id, $subscription_roles)
    {
        try {
            $tenant_external_id = get_post_meta($subscription_id, WPCSTenant::WPCS_TENANT_EXTERNAL_ID_META, true);
            $this->wpcsService->update_tenant($tenant_external_id, [
                'php_constants' => ['WPCS_TENANT_ROLES' => ['value' => implode(",", $subscription_roles), 'isPrivate' => false]]
            ]);
            update_post_meta($subscription_id, WPCSTenant::WPCS_SUBSCRIPTION_USER_ROLES, array_unique($subscription_roles));
        } catch (\Exception $error) {
            error_log('Failed to update roles list for tenant');
            error_log($error);
        }
    }
}
