<?php

namespace WaaSHost\Features;

class SingleLoginService
{
    public static function get_login_link($subscription_id, \WC_Order $order)
    {
        $email = $order->get_billing_email();
        return '/wp-json/' . PluginBootstrap::API_V1_NAMESPACE . '/tenant/single_login?subscription_id=' . $subscription_id . '&email=' . urlencode($email);
    }
}
