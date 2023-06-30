<?php

namespace WaaSHost\Features;

class AdminWpcsSettings
{
    const SHOULD_SEND_TENANT_READY_EMAIL_OPTION = 'wpcs_notification_settings_send_tenant_ready_email';

    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_wpcs_admin_page'], 12);
        add_action('admin_init', [$this, 'add_wpcs_admin_settings']);
        add_filter('wpcs_tenant_ready_email_allowed', [$this, 'allow_tenant_ready_email']);
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
}
