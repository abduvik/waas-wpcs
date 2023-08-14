<?php

namespace WaaSHost\Features;

class SingleLoginService
{
    public static function get_formatted_username($subscription_id)
    {
        $username = apply_filters('wpcs_get_customer_username_by_subscription_id', null, $subscription_id);
        return str_replace('-', '_', sanitize_title_with_dashes(remove_accents($username)));
    }

    public static function get_login_link($subscription_id, \WC_Order $order)
    {
        return '/wp-json/' . PluginBootstrap::API_V1_NAMESPACE . '/tenant/single_login?subscription_id=' . $subscription_id . "&_wpnonce=" . wp_create_nonce('wp_rest');
    }
}
