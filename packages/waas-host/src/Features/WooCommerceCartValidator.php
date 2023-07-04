<?php

namespace WaaSHost\Features;

use WaaSHost\Core\WPCSProduct;

class WooCommerceCartValidator
{
    public function __construct()
    {
        add_action('woocommerce_check_cart_items', [$this, 'validate_cart_contents']);
    }

    public function validate_cart_contents()
    {
        $cart = \WC()->cart;

        // No checks required
        if($cart->is_empty())
        {
            return;
        }

        $items = \WC()->cart->cart_contents;

        $cart_contains_add_on = false;
        $cart_contains_base_product = false;

        foreach ($items as $key => $item)
        {
            $wpcs_product = new WPCSProduct($item['product_id']);
            if($wpcs_product->is_wpcs_product())
            {
                if($wpcs_product->get_type() === WPCSProduct::WPCS_PRODUCT_TYPE_BASE_PRODUCT)
                {
                    $cart_contains_base_product = true;
                }

                if($wpcs_product->get_type() === WPCSProduct::WPCS_PRODUCT_TYPE_ADDON)
                {
                    $cart_contains_add_on = true;
                }
            }
        }

        if($cart_contains_add_on && !$cart_contains_base_product)
        {
            $text = __('Cannot proceed to checkout with an Add-on but no base product. Please add a base product to your cart.', WPCS_WAAS_HOST_TEXTDOMAIN);

            // Needs to be of type error, otherwise checkout is not prevented.
            \wc_add_notice($text, 'error');
        }
    }
}
