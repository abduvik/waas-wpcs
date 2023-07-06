<?php

namespace WaaSHost\Features;

use WaaSHost\Core\WPCSService;

class AdminWpcsSettings
{
    const SHOULD_SEND_TENANT_READY_EMAIL_OPTION = 'wpcs_notification_settings_send_tenant_ready_email';
    const SHOULD_REMOVE_ADMINISTRATOR_PLUGIN_CAPABILITIES_OPTION = 'wpcs_host_setting_remove_admininistrator_plugin_capabilities_on_tenant';

    private WPCSService $wpcsService;

    public function __construct(WPCSService $wpcsService)
    {
        $this->wpcsService = $wpcsService;
        add_action('admin_menu', [$this, 'add_wpcs_admin_page'], 12);
        add_action('admin_init', [$this, 'add_wpcs_admin_settings']);
        add_filter('wpcs_tenant_ready_email_allowed', [$this, 'allow_tenant_ready_email']);
        add_filter('wpcs_remove_tenant_administrator_plugin_capabilities', [$this, 'remove_tenant_administrator_plugin_capabilities']);
    }

    public function add_wpcs_admin_page()
    {
        add_submenu_page(
            'wpcs-admin',
            __('WPCS.io settings', WPCS_WAAS_HOST_TEXTDOMAIN),
            __('Settings', WPCS_WAAS_HOST_TEXTDOMAIN),
            'manage_options',
            'wpcs-admin-settings',
            [$this, 'render_wpcs_admin_page'],
        );
    }

    public function render_wpcs_admin_page()
    {
        $is_wpcs_api_setup = AdminWpcsHome::do_api_creds_exist();
        $can_reach_api = get_option('WPCS_CAN_REACH_API', false);

        if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
            if ($is_wpcs_api_setup) {
                try {
                    $this->wpcsService->can_reach_api();
                    update_option('WPCS_CAN_REACH_API', true);
                    $can_reach_api = true;
                } catch (\Exception $ei) {
                    update_option('WPCS_CAN_REACH_API', false);
                    echo $this->wpcs_could_not_connect_to_api_notice_error();
                    $can_reach_api = false;
                }
            }
        } elseif ($is_wpcs_api_setup && !$can_reach_api) {
            echo $this->wpcs_could_not_connect_to_api_notice_error();
        }

        echo '<h1>WPCS.io Admin</h1><form method="POST" action="options.php">';
        settings_fields('wpcs-admin');
        do_settings_sections('wpcs-admin');
        submit_button();
        echo '</form>';
    }

    public function add_wpcs_admin_settings()
    {
        add_settings_section(
            'wpcs_credentials',
            __('WPCS Credentials', WPCS_WAAS_HOST_TEXTDOMAIN),
            fn () => "<p>Intro text for our settings section</p>",
            'wpcs-admin'
        );

        register_setting('wpcs-admin', 'wpcs_credentials_region_setting');
        add_settings_field(
            'wpcs_credentials_region_setting',
            __('WPCS Region', WPCS_WAAS_HOST_TEXTDOMAIN),
            [$this, 'render_settings_field'],
            'wpcs-admin',
            'wpcs_credentials',
            [
                "id" => "wpcs_credentials_region_setting",
                "title" => __("WPCS Region", WPCS_WAAS_HOST_TEXTDOMAIN),
                "type" => "select",
                "choices" => [
                    'eu1' => __('EU1', WPCS_WAAS_HOST_TEXTDOMAIN),
                    'us1' => __('US1', WPCS_WAAS_HOST_TEXTDOMAIN),
                ],
            ]
        );

        register_setting('wpcs-admin', 'wpcs_credentials_api_key_setting');
        add_settings_field(
            'wpcs_credentials_api_key_setting',
            __('WPCS API Key', WPCS_WAAS_HOST_TEXTDOMAIN),
            [$this, 'render_settings_field'],
            'wpcs-admin',
            'wpcs_credentials',
            [
                "id" => "wpcs_credentials_api_key_setting",
                "title" => __("WPCS API Key", WPCS_WAAS_HOST_TEXTDOMAIN),
                "type" => "text"
            ]
        );

        register_setting('wpcs-admin', 'wpcs_credentials_api_secret_setting');
        add_settings_field(
            'wpcs_credentials_api_secret_setting',
            __('WPCS API Secret', WPCS_WAAS_HOST_TEXTDOMAIN),
            [$this, 'render_settings_field'],
            'wpcs-admin',
            'wpcs_credentials',
            [
                "id" => "wpcs_credentials_api_secret_setting",
                "title" => __("WPCS API Secret", WPCS_WAAS_HOST_TEXTDOMAIN),
                "type" => "password"
            ]
        );

        add_settings_section(
            'wpcs_host_settings',
            'WPCS Host Settings',
            fn () => "<p>Intro text for our settings section</p>",
            'wpcs-admin'
        );

        register_setting('wpcs-admin', 'wpcs_host_settings_root_domain');
        add_settings_field(
            'wpcs_host_settings_root_domain',
            __('Tenants Root Domain', WPCS_WAAS_HOST_TEXTDOMAIN),
            [$this, 'render_settings_field'],
            'wpcs-admin',
            'wpcs_host_settings',
            [
                "id" => "wpcs_host_settings_root_domain",
                "title" => __("Tenants Root Domain", WPCS_WAAS_HOST_TEXTDOMAIN),
                "type" => "text"
            ]
        );

        register_setting('wpcs-admin', 'wpcs_host_settings_default_user_role', [
            'default' => 'administrator',
            'sanitize_callback' => [$this, 'sanitize_user_role']
        ]);
        add_settings_field(
            'wpcs_host_settings_default_user_role',
            __("The user role of user that gets created", WPCS_WAAS_HOST_TEXTDOMAIN),
            [$this, 'render_settings_field'],
            'wpcs-admin',
            'wpcs_host_settings',
            [
                "id" => "wpcs_host_settings_default_user_role",
                "title" => __("The user role of user that gets created", WPCS_WAAS_HOST_TEXTDOMAIN),
                "type" => "text"
            ]
        );

        register_setting(
            'wpcs-admin',
            self::SHOULD_REMOVE_ADMINISTRATOR_PLUGIN_CAPABILITIES_OPTION,
            [
                'default' => 'on'
            ]
        );
        add_settings_field(
            self::SHOULD_REMOVE_ADMINISTRATOR_PLUGIN_CAPABILITIES_OPTION,
            __('Remove Administrator Plugin Capabilities on tenant creation?', WPCS_WAAS_HOST_TEXTDOMAIN),
            [$this, 'render_settings_field'],
            'wpcs-admin',
            'wpcs_host_settings',
            [
                "id" => self::SHOULD_REMOVE_ADMINISTRATOR_PLUGIN_CAPABILITIES_OPTION,
                "title" => __('Remove Administrator Plugin Capabilities on tenant creation?', WPCS_WAAS_HOST_TEXTDOMAIN),
                "type" => "checkbox",
                "value" => "on"
            ]
        );

        add_settings_section(
            'wpcs_notification_settings',
            __('WPCS Notification Settings', WPCS_WAAS_HOST_TEXTDOMAIN),
            fn () => "",
            'wpcs-admin'
        );

        register_setting('wpcs-admin', self::SHOULD_SEND_TENANT_READY_EMAIL_OPTION);
        add_settings_field(
            self::SHOULD_SEND_TENANT_READY_EMAIL_OPTION,
            __('Send E-mail when tenant is ready?', WPCS_WAAS_HOST_TEXTDOMAIN),
            [$this, 'render_settings_field'],
            'wpcs-admin',
            'wpcs_notification_settings',
            [
                "id" => self::SHOULD_SEND_TENANT_READY_EMAIL_OPTION,
                "title" => __('Send E-mail when tenant is ready?', WPCS_WAAS_HOST_TEXTDOMAIN),
                "type" => "checkbox",
                "value" => "on"
            ]
        );
    }

    function render_settings_field($args)
    {
        switch ($args['type']) {
            case 'checkbox':
                echo "<input type='{$args["type"]}' id='{$args["id"]}' name='{$args["id"]}' " . checked(get_option($args["id"], "on"), "on", false) . ">";
                break;
            case 'select':
                $current_value = get_option($args["id"]);
                echo "<select id='{$args["id"]}' name='{$args["id"]}'>";
                foreach ($args['choices'] as $value => $label) {
                    $selected = selected($current_value, $value, false);
                    echo "<option value='{$value}' {$selected}>{$label}</option>";
                }
                echo "</select>";
                break;
            default:
                echo "<input type='{$args["type"]}' id='{$args["id"]}' name='{$args["id"]}' value='" . get_option($args["id"]) . "'>";
                break;
        }
    }

    function allow_tenant_ready_email($allowed)
    {
        // If somebody else disallowed, respect their decision.
        if (!$allowed) {
            return $allowed;
        }

        return get_option(self::SHOULD_SEND_TENANT_READY_EMAIL_OPTION, 'off') === 'on';
    }

    function remove_tenant_administrator_plugin_capabilities($allowed)
    {
        // If somebody else disallowed, respect their decision.
        if (!$allowed) {
            return $allowed;
        }

        return get_option(self::SHOULD_REMOVE_ADMINISTRATOR_PLUGIN_CAPABILITIES_OPTION, 'off') === 'on';
    }

    public function wpcs_could_not_connect_to_api_notice_error()
    {
        echo '<div class="notice notice-error is-dismissible">
        <p>Important: your API Key/Secret combination or selected region seem to be incorrect, we could not connect.</p>
        </div>';
    }

    public function sanitize_user_role($value)
    {
        $trimmed = trim($value);
        return empty($trimmed) ? 'administrator' : $trimmed;
    }
}
