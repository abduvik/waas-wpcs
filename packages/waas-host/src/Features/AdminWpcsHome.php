<?php

namespace WaaSHost\Features;

class AdminWpcsHome
{
    public static function init()
    {
        add_action('admin_menu', [__CLASS__, 'add_wpcs_admin_page'], 11);
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
    }
}
