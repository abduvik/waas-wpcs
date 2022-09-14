<?php

namespace WaaSClient\Features;

class RolesManager
{
    public function __construct()
    {
        add_action('init', [$this, 'activate_enabled_plugins']);
    }

    public function activate_enabled_plugins(): void
    {
        $external_id = get_option(PluginBootstrap::EXTERNAL_ID, '');

        if ($external_id === '' || !file_exists(AdminRolesSettings::ROLES_FILE_PATH)) {
            // This is a WPCS version and not a tenant or plugin not yet setup
            return;
        }

        $roles_plugins = json_decode(file_get_contents(AdminRolesSettings::ROLES_FILE_PATH), true);
        $user_roles = get_option(PluginBootstrap::TENANT_ROLES, []);

        $enabled_plugins = [];
        foreach ($user_roles as $user_role) {
            if (!isset($roles_plugins[$user_role])) continue;
            $enabled_plugins = array_merge($enabled_plugins, $roles_plugins[$user_role]['plugins']);
        }

        $all_plugins = array_keys(get_plugins());
        $enabled_plugins = array_unique($enabled_plugins);
        $disabled_plugins = array_diff($all_plugins, $enabled_plugins);

        activate_plugins($enabled_plugins);

        $disabled_plugins = array_filter($disabled_plugins, fn($item) => $item !== PluginBootstrap::PLUGIN_NAME);
        deactivate_plugins($disabled_plugins);
    }
}