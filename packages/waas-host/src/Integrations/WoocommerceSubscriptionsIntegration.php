<?php

namespace WaaSHost\Integrations;

class WoocommerceSubscriptionsIntegration
{
    public static function init()
    {
        add_action('woocommerce_checkout_subscription_created', [__CLASS__, 'create_tenant_when_subscription_created'], 10, 2);
        add_action('woocommerce_subscription_status_cancelled', [__CLASS__, 'remove_tenant_when_subscription_expired']);
        add_action('woocommerce_subscription_details_table', [__CLASS__, 'after_subscription_details_html']);
        add_filter('wpcs_subscription_id_email_for_login_guard', [__CLASS__, 'subscription_id_to_email_filter'], 10, 2);
    }

    public static function create_tenant_when_subscription_created(\WC_Subscription $subscription, \WC_Order $order)
    {
        do_action('wpcs_subscription_created', $subscription->get_id(), $order);
    }

    public static function remove_tenant_when_subscription_expired(\WC_Subscription $subscription)
    {
        do_action('wpcs_subscription_expired', $subscription->get_id());
    }

    public static function after_subscription_details_html(\WC_Subscription $subscription)
    {
        $order = $subscription->get_parent();
        do_action('wpcs_after_subscription_details_html', $subscription->get_id(), $order);
    }

    public static function subscription_id_to_email_filter($value, $subscription_id)
    {
        $subscription = new \WC_Subscription($subscription_id);
        $order = $subscription->get_parent();
        $order->get_billing_email();
    }
}
