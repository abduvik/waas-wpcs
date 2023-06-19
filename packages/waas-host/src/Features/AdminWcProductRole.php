<?php

namespace WaaSHost\Features;

use WaaSHost\Core\WPCSTenant;

class AdminWcProductRole
{
    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'create_woocommerce_wpcs_versions_selector']);
        add_action('save_post', [$this, 'save_woocommerce_wpcs_versions_selector']);
    }

    public function create_woocommerce_wpcs_versions_selector()
    {
        add_meta_box(
            'wpcs_product_version_selector',
            'Roles/Product Mapper',
            [$this, 'render_woocommerce_product_role_mapper_selector'],
            'product',
            'side',
            'high'
        );

        add_meta_box(
            'wpcs_product_groupname_selector',
            'Snapshot mapper',
            [$this, 'render_woocommerce_group_name_input'],
            'product',
            'side',
            'high'
        );
    }

    public function render_woocommerce_product_role_mapper_selector($post)
    {
        $available_roles = get_option(PluginBootstrap::ROLES_WP_OPTION);
        $selected_role = get_post_meta($post->ID, WPCSTenant::WPCS_PRODUCT_ROLE_META, true);

        echo '<label for="wporg_field">Available Roles</label>';
        echo "<select name='" . WPCSTenant::WPCS_PRODUCT_ROLE_META . "' class='postbox'>";
        echo '<option value="">-- Select Role --</option>';
        foreach ($available_roles as $role_name => $role_data) {
            echo "<option " . selected($role_name, $selected_role) . " value='$role_name'>$role_data->title</option>";
        }
        echo '</select>';
    }

    public function render_woocommerce_group_name_input($post)
    {
        $group_name = get_post_meta($post->ID, WPCSTenant::WPCS_PRODUCT_GROUPNAME_META, true);
        echo '<label for="' . WPCSTenant::WPCS_PRODUCT_GROUPNAME_META . '">Tenant Snapshot Groupname</label>';
        echo "<input name='" . WPCSTenant::WPCS_PRODUCT_GROUPNAME_META . "' class='postbox' type='text' value='" . $group_name . "' />";
    }


    public function save_woocommerce_wpcs_versions_selector($post_id)
    {
        if ($_POST['post_type'] === 'product') {
            if (array_key_exists(WPCSTenant::WPCS_PRODUCT_ROLE_META, $_POST)) {
                update_post_meta(
                    $post_id,
                    WPCSTenant::WPCS_PRODUCT_ROLE_META,
                    $_POST[WPCSTenant::WPCS_PRODUCT_ROLE_META]
                );
            }

            if (array_key_exists(WPCSTenant::WPCS_PRODUCT_GROUPNAME_META, $_POST)) {
                $group_name = $_POST[WPCSTenant::WPCS_PRODUCT_GROUPNAME_META];
                if (strlen($group_name) > 0) {
                    update_post_meta(
                        $post_id,
                        WPCSTenant::WPCS_PRODUCT_GROUPNAME_META,
                        $group_name
                    );
                }
            }
        }
    }
}
