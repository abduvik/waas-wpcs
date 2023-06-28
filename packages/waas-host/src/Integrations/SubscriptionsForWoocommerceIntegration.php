<?php

namespace WaaSHost\Integrations;

class SubscriptionsForWoocommerceIntegration
{
    public static function init()
    {
        if(is_plugin_active('subscriptions-for-woocommerce/subscriptions-for-woocommerce.php'))
        {
            add_action('wps_sfw_after_created_subscription', [__CLASS__, 'create_tenant_when_subscription_created'], 10, 2);
            add_action('wps_sfw_subscription_cancel', [__CLASS__, 'remove_tenant_when_subscription_expired']);
            add_action('wps_sfw_after_subscription_details', [__CLASS__, 'ater_subscription_details_html'], 100);
            add_filter('wpcs_subscription_id_email_for_login_guard', [__CLASS__, 'subscription_id_to_email_filter'], 10, 2);
        }
    }

    public static function create_tenant_when_subscription_created($subscription_id, $order_id)
    {
        $order = new \WC_Order($order_id);
        do_action('wpcs_subscription_created', $subscription_id, $order);
    }

    public static function remove_tenant_when_subscription_expired($subscription_id)
    {
        do_action('wpcs_subscription_expired', $subscription_id);
    }

    public static function ater_subscription_details_html($subscription_id)
    {
        $order = new \WC_Order( $subscription_id );
        do_action('wpcs_after_subscription_details_html', $subscription_id, $order);
    }

    public static function subscription_id_to_email_filter($value, $subscription_id)
    {
        $order = new \WC_Order($subscription_id);
        return $order->get_billing_email();
    }
}
