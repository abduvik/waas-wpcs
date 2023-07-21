<?php

namespace WaaSHost\Features;

use WaaSHost\Core\WPCSProduct;

class AdminWpcsHome
{
    public static function init()
    {
        add_action('admin_menu', [__CLASS__, 'add_wpcs_admin_page'], 11);

        add_filter('wpcs_getting_started_checklist', [__CLASS__, 'check_api_creds']);
        add_filter('wpcs_getting_started_checklist', [__CLASS__, 'check_required_plugins']);
        add_filter('wpcs_getting_started_checklist', [__CLASS__, 'check_tenant_roles']);
        add_filter('wpcs_getting_started_checklist', [__CLASS__, 'check_woo_wpcs_product']);
    }

    public static function do_api_creds_exist() {
        $region_exists = defined('WPCS_API_REGION') && WPCS_API_REGION !== false;
        $key_exists = defined('WPCS_API_KEY') && WPCS_API_KEY !== false;
        $secret_exists = defined('WPCS_API_SECRET') && WPCS_API_SECRET !== false;
        return $region_exists && $key_exists && $secret_exists;
    }

    public static function check_api_creds($checklist_items)
    {
        $checklist_items['wpcs_credentials']['is_done'] = static::do_api_creds_exist();
        return $checklist_items;
    }

    public static function check_required_plugins($checklist_items)
    {
        // Is WooCommerce installed and active?
        $woocommerce_active = is_plugin_active( 'woocommerce/woocommerce.php');
        
        $subs_for_wc_active = is_plugin_active('subscriptions-for-woocommerce/subscriptions-for-woocommerce.php');
        $wc_subs_active = is_plugin_active('woocommerce-subscriptions/woocommerce-subscriptions.php');

        $checklist_items['required_plugins_installed']['is_done'] = $woocommerce_active && ($subs_for_wc_active || $wc_subs_active);

        return $checklist_items;
    }

    public static function check_tenant_roles($checklist_items)
    {
        $roles = get_option(PluginBootstrap::ROLES_WP_OPTION);
        $checklist_items['setup_tenant_roles']['is_done'] = $roles && count(array_keys((array)$roles)) > 0;

        return $checklist_items;
    }

    public static function check_woo_wpcs_product($checklist_items)
    {
        $products = get_posts([
            'post_type' => 'product',
            'meta_query' => [WPCSProduct::get_wpcs_product_meta_query()],
        ]);

        $checklist_items['create_woo_wpcs_product']['is_done'] = count($products) > 0;

        return $checklist_items;
    }

    public static function add_wpcs_admin_page()
    {
        $cap = 'manage_options';
        $slug = 'wpcs-admin';
        add_menu_page(
            __('WPCS.io', WPCS_WAAS_HOST_TEXTDOMAIN),
            __('WPCS.io', WPCS_WAAS_HOST_TEXTDOMAIN),
            $cap,
            $slug,
            [__CLASS__, 'render_wpcs_admin_page'],
            'dashicons-networking',
            10
        );

        add_submenu_page(
            $slug, // parent menu slug
            __('WPCS.io', WPCS_WAAS_HOST_TEXTDOMAIN), // page title
            __('Getting Started', WPCS_WAAS_HOST_TEXTDOMAIN), // menu title
            $cap, // capability
            $slug, // menu slug
            [__CLASS__, 'render_wpcs_admin_page'] // callback function
        );
    }

    public static function render_wpcs_admin_page()
    {
        ?>
        <div style="max-width:50vw" class="wpcs-container">
            <h1>WPCS.io Admin</h1>
            <section>
                <h2>Welcome to the WPCS Storefront!</h2>
                <p>
                    Before you can sell sites automatically with this Storefront there are a few things that require setup. Down below you can find a list of things to set up in the Storefront here.
                    But maybe even more important is to have something to sell.
                    The Storefront is only useful when connecting it to a WPCS Application that contains a Version (the one with the production label) with the WaaS-Client plugin setup. <a href="https://github.com/Daxez/waas-wpcs/releases/latest" target="_blank">You can download the WaaS-Client plugin here.</a>
                </p>

            </section>
        <?php

        $default_checklist = [
            "wpcs_credentials" => [
                "label" => __('Fill out your WPCS API credentials', WPCS_WAAS_HOST_TEXTDOMAIN),
                "is_done" => false,
            ],
            "required_plugins_installed" => [
                "label" => __('Install the required plugins', WPCS_WAAS_HOST_TEXTDOMAIN),
                "is_done" => false,
            ],
            "create_woo_wpcs_product" => [
                "label" => __('Create a WooCommerce WPCS product', WPCS_WAAS_HOST_TEXTDOMAIN),
                "is_done" => false,
            ],
            "setup_tenant_roles" => [
                "label" => __('Create some tenant roles', WPCS_WAAS_HOST_TEXTDOMAIN),
                "is_done" => false,
            ],
        ];
        $checklist_items = apply_filters('wpcs_getting_started_checklist', $default_checklist);

        ?>
            <section>
                <ul class="ticks">
                    <?php foreach ($checklist_items as $id => $checklist_item): ?>
                        <li class="<?php echo $checklist_item['is_done'] ? "checked" : "unchecked"; ?>">
                            <span><?php echo $checklist_item['label'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
            <section>
                <h2>Tutorial</h2>
                <p>You can check out our tutorial on <a href="https://wpcs.io/knowledge-base/create-a-waas-storefront-in-less-than-1-hour-with-simple-wp-plugins/" target="_blank">how to set up your WaaS on WPCS.io here</a>, or you can watch the video below.</p>
                <iframe width="560" height="315" src="https://www.youtube.com/embed/YZ1igbOMTT4" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
            </section>
        </div>
        <?php
    }
}
