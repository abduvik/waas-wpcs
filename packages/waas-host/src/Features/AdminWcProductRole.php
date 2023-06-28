<?php

namespace WaaSHost\Features;

use WaaSHost\Core\WPCSTenant;
use WaaSHost\Core\WPCSService;

class AdminWcProductRole
{
    private WPCSService $wpcsService;

    public function __construct(WPCSService $wpcsService)
    {
        $this->wpcsService = $wpcsService;
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
        echo '<p><a href="' . admin_url('admin-post.php?action=wpcs_refresh_roles') . '">Refresh Roles</a></p>';
    }

    public function render_woocommerce_group_name_input($post)
    {
        $selected_group_name = get_post_meta($post->ID, WPCSTenant::WPCS_PRODUCT_GROUPNAME_META, true);
        $available_groupnames = $this->wpcsService->get_available_groupnames();
        echo "<label for='" . WPCSTenant::WPCS_PRODUCT_GROUPNAME_META . "'>Tenant Snapshot Groupname</label>";

        echo "<select name='" . WPCSTenant::WPCS_PRODUCT_GROUPNAME_META . "' class='postbox'>";
        echo '<option ' . selected("", $selected_group_name) . 'value="">-- No Tenant Snapshot --</option>';
        foreach ($available_groupnames as $group_name) {
            echo "<option " . selected($group_name, $selected_group_name) . " value='$group_name'>$group_name</option>";
        }
        echo '</select>';
    }


    public function save_woocommerce_wpcs_versions_selector($post_id)
    {
        if (array_key_exists('post_type', $_POST) && $_POST['post_type'] === 'product')
        {
            if (array_key_exists(WPCSTenant::WPCS_PRODUCT_ROLE_META, $_POST)) {
                update_post_meta(
                    $post_id,
                    WPCSTenant::WPCS_PRODUCT_ROLE_META,
                    $_POST[WPCSTenant::WPCS_PRODUCT_ROLE_META]
                );
            }

            if (array_key_exists(WPCSTenant::WPCS_PRODUCT_GROUPNAME_META, $_POST)) {
                $group_name = $_POST[WPCSTenant::WPCS_PRODUCT_GROUPNAME_META];
                update_post_meta(
                    $post_id,
                    WPCSTenant::WPCS_PRODUCT_GROUPNAME_META,
                    $group_name
                );
            }
        }
    }
}
