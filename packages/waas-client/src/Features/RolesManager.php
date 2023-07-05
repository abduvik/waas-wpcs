<?php

namespace WaaSClient\Features;

class RolesManager
{
    public function __construct()
    {
        add_action('init', [$this, 'activate_enabled_plugins']);

        if (defined(PluginBootstrap::TENANT_ROLES_CONSTANT)) {
            add_filter('option_' . PluginBootstrap::TENANT_ROLES, [$this, 'get_roles_from_constant']);
            add_filter('default_option_' . PluginBootstrap::TENANT_ROLES, [$this, 'get_roles_from_constant']);
        }
    }

    public function activate_enabled_plugins(): void
    {
        if (getenv('WPCS_IS_TENANT') !== 'true') {
            // This is a not a WPCS tenant. Either we're in a version or some local environment
            return;
        }
        if (!file_exists(AdminRolesSettings::ROLES_FILE_PATH)) {
            // This is a WPCS tenant but the Plugin is not yet setup or the Version is not deployed
            add_action('admin_notices', [$this, 'wpcs_no_roles_json_found_admin_notice']);
            return;
        }

        $user_roles = get_option(PluginBootstrap::TENANT_ROLES, []);
        $roles_plugins = json_decode(file_get_contents(AdminRolesSettings::ROLES_FILE_PATH), true);

        if (empty($user_roles)) {
            // Someone created a tenant without a role. Either they are testing, made a mistake or are not familiar with the plugins.
            // Probably best to just notify them and leave it for now.
            add_action('admin_notices', $this->get_wpcs_no_role_defined_admin_notice($roles_plugins));
            return;
        }

        $enabled_plugins = [];
        foreach ($user_roles as $user_role) {
            if (!isset($roles_plugins[$user_role])) continue;
            $enabled_plugins = array_merge($enabled_plugins, $roles_plugins[$user_role]['plugins']);
        }

        $all_plugins = array_keys(get_plugins());
        $enabled_plugins = array_unique($enabled_plugins);
        $disabled_plugins = array_diff($all_plugins, $enabled_plugins);

        $plugins_requiring_enabling = array_values(array_filter($enabled_plugins, fn ($plugin) => !is_plugin_active($plugin)));
        activate_plugins($plugins_requiring_enabling);

        $disabled_plugins = array_filter($disabled_plugins, fn ($item) => $item !== PluginBootstrap::PLUGIN_NAME && is_plugin_active($item));
        deactivate_plugins($disabled_plugins);
    }

    public function get_roles_from_constant()
    {
        return array_map('trim', explode(',', constant(PluginBootstrap::TENANT_ROLES_CONSTANT)));
    }

    public function wpcs_no_roles_json_found_admin_notice() {
?>
            <div class="notice notice-error is-dismissible">
                <p><b><?php _e('It looks like we are in a WPCS Tenant with the WaaS-Client plugin enabled but it seems that there are no Roles defined.', WPCS_WAAS_CLIENT_TEXTDOMAIN); ?></b></p>
                <p><?php _e('If there are no roles defined in the Version that should be setup first.', WPCS_WAAS_CLIENT_TEXTDOMAIN); ?></p>
                <p><?php _e('If there are roles defined in the Version this Tenant is running on please make sure to deploy.', WPCS_WAAS_CLIENT_TEXTDOMAIN); ?></p>
            </div>
<?php
    }

    public function get_wpcs_no_role_defined_admin_notice($roles)
    {
        return function () use ($roles) {
?>
            <div class="notice notice-error is-dismissible">
                <p><b><?php _e('It looks like we are in a WPCS Tenant with the WaaS-Client plugin enabled but there is no Role defined for this tenant.', WPCS_WAAS_CLIENT_TEXTDOMAIN); ?></b></p>
                <p><?php _e('This can happen when the Tenant has been created manually through the WPCS Console.', WPCS_WAAS_CLIENT_TEXTDOMAIN); ?></p>
                <p><?php _e('To test out the roles that are defined in the Version, a PHP Constant can be added to the Tenant.', WPCS_WAAS_CLIENT_TEXTDOMAIN); ?></p>
                <p><?php _e('The PHP Constant should have the name ' . PluginBootstrap::TENANT_ROLES_CONSTANT . ' and one or more of the following roles separated by a \',\'', WPCS_WAAS_CLIENT_TEXTDOMAIN); ?></p>
                <ul>
                    <?php
                    foreach ($roles as $key => $value) {
                        echo "<li><i>$key</i></li>\n";
                    }
                    ?>
                </ul>
            </div>
<?php
        };
    }
}
