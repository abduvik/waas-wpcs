<?php

namespace WaaSHost\Integrations\SelfServiceDashboard;

class SelfServiceDashboardIntegration
{
    public static function init()
    {
        if (!function_exists('is_plugin_active'))
        {
            require_once(ABSPATH . '/wp-admin/includes/plugin.php');
        }

        if (is_plugin_active('self-service-dashboard-for-woocommerce-subscriptions/self-service-dashboard-for-woocommerce-subscriptions.php'))
        {
            add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_addon_loading_indicator']);
        }
    }

    public static function enqueue_addon_loading_indicator()
    {
        if(\is_account_page())
        {
            $script_name = 'add-on-loading-indicator.js';
            $src = plugin_dir_url( __FILE__ ) . "/assets/js/$script_name";
            $version = date("ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . "assets/js/$script_name" ));
            wp_enqueue_script('add-on-loading-indicator', $src, ['jquery'], $version, true);
        }
    }
}
