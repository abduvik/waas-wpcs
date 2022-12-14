<?php

namespace WaaSHost\Features;

use WaaSHost\Core\WPCSTenant;

class UserWcTenantsCheckout
{
    public function __construct()
    {
        add_filter('woocommerce_checkout_fields', [$this, 'render_wpcs_checkout_fields']);
        add_action('woocommerce_checkout_update_order_meta', [$this, 'add_wpcs_checkout_fields']);
    }

    public function render_wpcs_checkout_fields($fields)
    {
        $fields['billing'][WPCSTenant::WPCS_WEBSITE_NAME_META] = [
            'label' => 'Website Name',
            'required' => true,
            'priority' => 20,
        ];

        return $fields;
    }

    function add_wpcs_checkout_fields($order_id)
    {
        update_post_meta($order_id, WPCSTenant::WPCS_WEBSITE_NAME_META, sanitize_text_field($_POST[WPCSTenant::WPCS_WEBSITE_NAME_META]));
    }
}