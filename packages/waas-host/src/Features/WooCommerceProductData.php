<?php

namespace WaaSHost\Features;

use WaaSHost\Core\WPCSProduct;
use WaaSHost\Core\WPCSService;

class WooCommerceProductData
{
    const WPCS_PRODUCT_DATA_TAB_TARGET = 'wpcs-product-settings';

    private WPCSService $wpcsService;

    public function __construct(WPCSService $wpcsService)
    {
        $this->wpcsService = $wpcsService;

        add_filter('woocommerce_product_data_tabs', [$this, 'register_product_tabs']);
        add_action('woocommerce_product_data_panels', [$this, 'display_product_tab_content']);
        add_action('woocommerce_process_product_meta', [$this, 'store_product_tab_data']);
        add_filter('product_type_options', [$this, 'display_product_type']);
        add_action('woocommerce_process_product_meta', [$this, 'store_product_type']);
    }

    public function register_product_tabs($tabs)
    {
        $tabs['wpcs'] = [
            'label' => __('WPCS', WPCS_WAAS_HOST_TEXTDOMAIN),
            'priority' => 50,
            'target' => self::WPCS_PRODUCT_DATA_TAB_TARGET,
            'class'    => ['show_if_simple'],
        ];

        return $tabs;
    }

    public function display_product_tab_content()
    {
        $is_wpcs_api_setup = AdminWpcsHome::do_api_creds_exist();
        if (!$is_wpcs_api_setup) {
?>
            <div id="<?php echo self::WPCS_PRODUCT_DATA_TAB_TARGET; ?>" class="panel woocommerce_options_panel">
                <div>
                    <p>
                        <?php _e("Looks like your API Credentials are not yet setup. Please click the link below to connect this storefont to an Applicaiton on WPCS!",WPCS_WAAS_HOST_TEXTDOMAIN) ?>
                    </p>
                    <a href="<?php echo admin_url('/admin.php?page=wpcs-admin-settings') ?>">
                        Setup API Credentials
                    </a>
                </div>
            </div>
        <?php
        }

        $role_options = [];
        foreach (get_option(PluginBootstrap::ROLES_WP_OPTION, []) as $role_slug => $role_data) {
            $role_options[$role_slug] = $role_data->title;
        }

        $available_groupnames = [
            '' => _x('None', 'Option name when no tenant snapshot chosen', WPCS_WAAS_HOST_TEXTDOMAIN),
        ];
        foreach ($this->wpcsService->get_available_groupnames() as $groupname) {
            $available_groupnames[$groupname] = $groupname;
        }

        ?>
        <div id="<?php echo self::WPCS_PRODUCT_DATA_TAB_TARGET; ?>" class="panel woocommerce_options_panel">
            <div>
                <?php
                // \woocommerce_wp_select([
                //     'id'            => WPCSProduct::WPCS_PRODUCT_TYPE_META,
                //     'label'         => __( 'Type', WPCS_WAAS_HOST_TEXTDOMAIN),
                //     'description'   => __( 'The type of this product, it\'s self explanatory really..', WPCS_WAAS_HOST_TEXTDOMAIN),
                //     'options'  		=> [
                //         WPCSProduct::WPCS_PRODUCT_TYPE_BASE_PRODUCT => __('Base product', WPCS_WAAS_HOST_TEXTDOMAIN),
                //         WPCSProduct::WPCS_PRODUCT_TYPE_ADDON => __('Add-on', WPCS_WAAS_HOST_TEXTDOMAIN),
                //     ],
                //     'desc_tip'    	=> false,
                // ]);
                ?>
            </div>
            <div>
                <?php \woocommerce_wp_select([
                    'id'            => WPCSProduct::WPCS_PRODUCT_ROLE_META,
                    'label'         => __('Role', WPCS_WAAS_HOST_TEXTDOMAIN),
                    'description'   => __('The role used to configure the WPCS Tenant with after purchase.', WPCS_WAAS_HOST_TEXTDOMAIN),
                    'options'          => $role_options,
                    'desc_tip'        => false,
                ]); ?>
            </div>
            <div>
                <p>
                    <a href="<?php echo admin_url('/admin-post.php?action=wpcs_refresh_roles') ?>">
                        Refresh Roles
                    </a>
                </p>
            </div>
            <div>
                <?php \woocommerce_wp_select([
                    'id'            => WPCSProduct::WPCS_PRODUCT_GROUPNAME_META,
                    'label'         => __('Tenant Snapshot Groupname', WPCS_WAAS_HOST_TEXTDOMAIN),
                    'description'   => __('The tenant snapshot groupname used to configure the WPCS Tenant with after purchase.', WPCS_WAAS_HOST_TEXTDOMAIN),
                    'options'          => $available_groupnames,
                    'desc_tip'        => false,
                ]); ?>
            </div>
        </div>
<?php
    }

    public function store_product_tab_data($post_id)
    {
        if (array_key_exists(WPCSProduct::WPCS_PRODUCT_ROLE_META, $_POST)) {
            update_post_meta(
                $post_id,
                WPCSProduct::WPCS_PRODUCT_ROLE_META,
                $_POST[WPCSProduct::WPCS_PRODUCT_ROLE_META]
            );
        }

        if (array_key_exists(WPCSProduct::WPCS_PRODUCT_TYPE_META, $_POST)) {
            $product = new WPCSProduct($post_id);
            $product->store_type($_POST[WPCSProduct::WPCS_PRODUCT_TYPE_META]);
        }

        if (array_key_exists(WPCSProduct::WPCS_PRODUCT_GROUPNAME_META, $_POST)) {
            update_post_meta(
                $post_id,
                WPCSProduct::WPCS_PRODUCT_GROUPNAME_META,
                $_POST[WPCSProduct::WPCS_PRODUCT_GROUPNAME_META]
            );
        }
    }

    public function display_product_type($product_type_options)
    {
        $product_type_options[WPCSProduct::IS_WPCS_PRODUCT_META] = [
            "id"            => WPCSProduct::IS_WPCS_PRODUCT_META,
            "wrapper_class" => "show_if_simple",
            "label"         => __("WPCS Product", WPCS_WAAS_HOST_TEXTDOMAIN),
            "description"   => __("WPCS Products use WPCS to provision and update Tenants running on WPCS.", WPCS_WAAS_HOST_TEXTDOMAIN),
            "default"       => "no",
        ];

        return $product_type_options;
    }

    public function store_product_type($post_id)
    {
        $wpcs_product = new WPCSProduct($post_id);
        $wpcs_product->store_is_wpcs_product(isset($_POST[WPCSProduct::IS_WPCS_PRODUCT_META]));
    }
}
