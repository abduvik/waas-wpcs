<?php

namespace WaaSHost\Features;

class SingleLoginService
{
    public static function get_login_link($subscription_id, \WC_Order $order)
    {
        return '/wp-json/' . PluginBootstrap::API_V1_NAMESPACE . '/tenant/single_login?subscription_id=' . $subscription_id . "&_wpnonce=" . wp_create_nonce('wp_rest');
    }
}
