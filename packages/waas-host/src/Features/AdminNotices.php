<?php

namespace WaaSHost\Features;

class AdminNotices
{
    const REFRESH_ROLES_FAILED_NOTICE = 'refresh_roles_failed_notice';

    public static function init()
    {
        add_action('admin_notices', [__CLASS__, 'show_refresh_roles_failed_notice']);
    }

    public static function show_refresh_roles_failed_notice()
    {
        $notice = get_transient(static::REFRESH_ROLES_FAILED_NOTICE);
        
        if ($notice) 
        {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $notice ) . '</p></div>';

            // Delete the transient after displaying the notice
            delete_transient(static::REFRESH_ROLES_FAILED_NOTICE);
        }
    }
}
