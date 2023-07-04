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

    public static function check_api_creds($checklist_items)
    {
        $region_exists = defined('WPCS_API_REGION') && WPCS_API_REGION !== false;
        $key_exists = defined('WPCS_API_KEY') && WPCS_API_KEY !== false;
        $secret_exists = defined('WPCS_API_SECRET') && WPCS_API_SECRET !== false;
        $api_creds_filled_out = $region_exists && $key_exists && $secret_exists;

        $checklist_items['wpcs_credentials']['is_done'] = $api_creds_filled_out;

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
        echo '<h1>WPCS.io Admin</h1>';
        echo '<p>Did you ever hear the tragedy of Darth Plagueis The Wise? I thought not. It’s not a story the Jedi would tell you. It’s a Sith legend. Darth Plagueis was a Dark Lord of the Sith, so powerful and so wise he could use the Force to influence the midichlorians to create life… He had such a knowledge of the dark side that he could even keep the ones he cared about from dying. The dark side of the Force is a pathway to many abilities some consider to be unnatural. He became so powerful… the only thing he was afraid of was losing his power, which eventually, of course, he did. Unfortunately, he taught his apprentice everything he knew, then his apprentice killed him in his sleep. Ironic. He could save others from death, but not himself.</p>';

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
        <style>
            ul.ticks {
               list-style: none;
            }

            ul.ticks span {
                position: relative;
                left: 1em;
            }

            ul.ticks li.checked:before {
                content: '\2713';
            }
            ul.ticks li.unchecked:before {
                content: '\25a2';
            }
        </style>
        <ul class="ticks">
            <?php foreach ($checklist_items as $id => $checklist_item): ?>
                <li class="<?php echo $checklist_item['is_done'] ? "checked" : "unchecked"; ?>">
                    <span><?php echo $checklist_item['label'] ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
    }
}
