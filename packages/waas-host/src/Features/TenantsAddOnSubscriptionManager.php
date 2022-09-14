<?php

namespace WaaSHost\Features;

use WaaSHost\Core\HttpService;
use WaaSHost\Core\WPCSTenant;

class TenantsAddOnSubscriptionManager
{
    public function __construct()
    {
        add_action('ssd_add_simple_product_before_calculate_totals', [$this, 'on_add_send_update_tenant_user_roles'], 20, 1);
        add_action('wcs_user_removed_item', [$this, 'on_remove_send_update_tenant_user_roles'], 20, 2);
    }

    public function on_add_send_update_tenant_user_roles(\WC_Subscription $subscription): void
    {
        $order_items = $subscription->get_items();
        $subscription_roles = [];
        foreach ($order_items as $order_Item) {
            $product_user_role = get_post_meta($order_Item->get_product_id(), WPCSTenant::WPCS_PRODUCT_ROLE_META, true);
            $subscription_roles[] = $product_user_role;
        }

        update_post_meta($subscription->get_id(), WPCSTenant::WPCS_SUBSCRIPTION_USER_ROLES, array_unique($subscription_roles));

        try {
            $tenant_base_domain = $subscription->get_meta(WPCSTenant::WPCS_BASE_DOMAIN_NAME_META);
            if (strpos($tenant_base_domain, 'http') !== 0) {
                $tenant_base_domain = 'https://' . $tenant_base_domain;
            }

            $http_service = new HttpService($tenant_base_domain);
            $http_service->get('/wp-json/waas-client/v1/user-role-plan/fetch-updated-list');

        } catch (\Exception $error) {
            error_log('Failed to update roles list for tenant');
            error_log($error);
        }
    }

    public function on_remove_send_update_tenant_user_roles(\WC_Order_Item_Product $line_item, \WC_Subscription $subscription): void
    {
        $order_items = $subscription->get_items();
        $subscription_roles = [];
        foreach ($order_items as $order_Item) {
            if ($order_Item->get_id() === $line_item->get_id()) {
                continue;
            }

            $product_user_role = get_post_meta($order_Item->get_product_id(), WPCSTenant::WPCS_PRODUCT_ROLE_META, true);
            $subscription_roles[] = $product_user_role;
        }

        update_post_meta($subscription->get_id(), WPCSTenant::WPCS_SUBSCRIPTION_USER_ROLES, array_unique($subscription_roles));

        try {
            $tenant_base_domain = $subscription->get_meta(WPCSTenant::WPCS_BASE_DOMAIN_NAME_META);
            if (strpos($tenant_base_domain, 'http') !== 0) {
                $tenant_base_domain = 'https://' . $tenant_base_domain;
            }

            $http_service = new HttpService($tenant_base_domain);
            $http_service->get('/wp-json/waas-client/v1/user-role-plan/fetch-updated-list');

        } catch (\Exception $error) {
            error_log('Failed to update roles list for tenant');
            error_log($error);
        }
    }
}