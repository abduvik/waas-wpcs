<?php

namespace WaaSHost\Integrations;

use WaaSHost\Core\WPCSTenant;
use WaaSHost\Features\SingleLoginService;

class SubscriptionsForWoocommerceIntegration
{
    public static function init()
    {
        if (!function_exists('is_plugin_active')) {
            require_once(ABSPATH . '/wp-admin/includes/plugin.php');
        }

        if (is_plugin_active('subscriptions-for-woocommerce/subscriptions-for-woocommerce.php')) {
            add_action('wps_sfw_order_status_changed', [__CLASS__, 'create_tenant_when_subscription_created'], 10, 2);
            add_action('wps_sfw_subscription_cancel', [__CLASS__, 'remove_tenant_when_subscription_expired']);
            add_action('wps_sfw_after_subscription_details', [__CLASS__, 'ater_subscription_details_html'], 100);
            add_filter('wpcs_subscription_id_email_for_login_guard', [__CLASS__, 'subscription_id_to_email_filter'], 10, 2);
            add_filter('wpcs_get_customer_id_by_subscription_id_for_login_guard', [__CLASS__, 'subscription_id_to_customer_id'], 10, 2);
            add_filter('wps_sfw_subscription_details_html', [__CLASS__, 'show_tenant_status'], 10, 1);
            add_filter('wps_sfw_subscription_details_html', [__CLASS__, 'show_login_link'], 10, 1);
            add_filter('wpcs_subscription_details_url', [__CLASS__, 'get_subscription_detail_page'], 10, 2);
        }
    }

    public static function create_tenant_when_subscription_created($order_id, $subscription_id)
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
        $order = new \WC_Order($subscription_id);
        do_action('wpcs_after_subscription_details_html', $subscription_id, $order);
    }

    public static function subscription_id_to_email_filter($value, $subscription_id)
    {
        $order = new \WC_Order($subscription_id);
        return $order->get_billing_email();
    }

    public static function subscription_id_to_customer_id($value, $subscription_id) {
        $order = new \WC_Order($subscription_id);
        $parent_order = new \WC_Order($order->get_parent_id());
        return $parent_order->get_customer_id();
    }

    public static function show_tenant_status($subscription_id)
    {
        $tenant = new WPCSTenant($subscription_id);
?>
        <tr>
            <td>Website status</td>
            <td><?php echo $tenant->get_status(); ?></td>
        </tr>
    <?php
    }

    public static function show_login_link($subscription_id)
    {
        $order = new \WC_Order($subscription_id);
        $login_link = SingleLoginService::get_login_link($subscription_id, $order);
    ?>
        <tr>
            <td colspan="2">
                <a href='<?php echo $login_link; ?>' target='_blank' class="wpcs-single-login-button">
                    Login as <?php echo $order->get_billing_email(); ?>
                </a>
            </td>
        </tr>
<?php
    }

    public static function get_subscription_detail_page($storefront_url, $subscription_id)
    {
        return "$storefront_url/my-account/show-subscription/$subscription_id";
    }
}
