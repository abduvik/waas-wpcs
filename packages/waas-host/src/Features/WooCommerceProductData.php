<?php

namespace WaaSHost\Features;

use WaaSHost\Core\WPCSProduct;
use WaaSHost\Core\WPCSTenant;

class WooCommerceProductData
{
    const WPCS_PRODUCT_DATA_TAB_TARGET = 'wpcs-product-settings';

    public static function init()
    {
        add_filter('woocommerce_product_data_tabs', [__CLASS__, 'register_product_tabs']);
        add_action('woocommerce_product_data_panels', [__CLASS__, 'display_product_tab_content']);
        add_action('woocommerce_process_product_meta', [__CLASS__, 'store_product_tab_data']);
        add_filter('product_type_options', [__CLASS__, 'display_product_type']);
        add_action('woocommerce_admin_process_product_object', [__CLASS__, 'store_product_type']);
    }

    public static function register_product_tabs($tabs)
    {
        $tabs['wpcs'] = [
            'label' => __('WPCS', WPCS_WAAS_HOST_TEXTDOMAIN),
            'priority' => 50,
            'target' => self::WPCS_PRODUCT_DATA_TAB_TARGET,
            'class'    => [ 'show_if_simple'],
        ];

        return $tabs;
    }

    public static function display_product_tab_content()
    {
        $options = [];
        foreach (get_option(PluginBootstrap::ROLES_WP_OPTION) as $role_slug => $role_data)
        {
            $options[$role_slug] = $role_data->title;
        }

        ?>
        <div id="<?php echo self::WPCS_PRODUCT_DATA_TAB_TARGET; ?>" class="panel woocommerce_options_panel">
            <div>
                <?php \woocommerce_wp_select([
                    'id'            => WPCSProduct::WPCS_PRODUCT_TYPE_META,
                    'label'         => __( 'Type', WPCS_WAAS_HOST_TEXTDOMAIN),
                    'description'   => __( 'The type of this product, it\'s self explanatory really..', WPCS_WAAS_HOST_TEXTDOMAIN),
                    'options'  		=> [
                        WPCSProduct::WPCS_PRODUCT_TYPE_BASE_PRODUCT => __('Base product', WPCS_WAAS_HOST_TEXTDOMAIN),
                        WPCSProduct::WPCS_PRODUCT_TYPE_ADDON => __('Add-on', WPCS_WAAS_HOST_TEXTDOMAIN),
                    ],
                    'desc_tip'    	=> false,
                ]); ?>
            </div>
            <div>
                <?php \woocommerce_wp_select([
                    'id'            => WPCSProduct::WPCS_PRODUCT_ROLE_META,
                    'label'         => __( 'Role', WPCS_WAAS_HOST_TEXTDOMAIN),
                    'description'   => __( 'Role? But like for a tenant y\'know?', WPCS_WAAS_HOST_TEXTDOMAIN),
                    'options'  		=> $options,
                    'desc_tip'    	=> false,
                ]); ?>
            </div>
            <div>
                <p>
                    <a href="<?php admin_url('admin-post.php?action=wpcs_refresh_roles') ?>">
                        Refresh Roles
                    </a>
                </p>
            </div>
        </div>
        <?php
    }

    public static function store_product_tab_data($post_id)
    {
        if (array_key_exists(WPCSProduct::WPCS_PRODUCT_ROLE_META, $_POST))
        {
            update_post_meta(
                $post_id,
                WPCSProduct::WPCS_PRODUCT_ROLE_META,
                $_POST[WPCSProduct::WPCS_PRODUCT_ROLE_META]
            );
        }

        if (array_key_exists(WPCSProduct::WPCS_PRODUCT_TYPE_META, $_POST))
        {
            update_post_meta(
                $post_id,
                WPCSProduct::WPCS_PRODUCT_TYPE_META,
                $_POST[WPCSProduct::WPCS_PRODUCT_TYPE_META]
            );
        }
    }

    public static function display_product_type($product_type_options)
    {
        $product_type_options[WPCSProduct::IS_WPCS_PRODUCT_META] = [
            "id"            => WPCSProduct::IS_WPCS_PRODUCT_META,
            "wrapper_class" => "show_if_simple",
            "label"         => __("WPCS Product", WPCS_WAAS_HOST_TEXTDOMAIN),
            "description"   => __("A website product using WPCS", WPCS_WAAS_HOST_TEXTDOMAIN),
            "default"       => "no",
        ];
    
        return $product_type_options;
    }

    public static function store_product_type($product)
    {
        if (array_key_exists(WPCSProduct::IS_WPCS_PRODUCT_META, $_POST))
        {
            update_post_meta(
                $product->get_id(),
                WPCSProduct::IS_WPCS_PRODUCT_META,
                isset($_POST[WPCSProduct::IS_WPCS_PRODUCT_META]) ? 'yes' : 'no'
            );
        }
    }
}
