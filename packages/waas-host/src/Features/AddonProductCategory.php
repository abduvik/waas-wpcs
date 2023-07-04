<?php

namespace WaaSHost\Features;

class AddonProductCategory
{
    public const TERM_SLUG = 'add-on';
    public const TERM_TAX_SLUG = 'product_cat';

    public static function init()
    {
        // add_action('woocommerce_check_cart_items', [__CLASS__, 'validate_all_cart_contents']);
    }

    public static function validate_all_cart_contents()
    {
        // empty cart? let WC handle checkout
        if(\WC()->cart->is_empty())
        {
            return;
        }

        $items = \WC()->cart->cart_contents;

        $cart_contains_add_on = false;
        $cart_contains_base_product = false;

        foreach ($items as $key => $item)
        {
            if(self::is_product_addon($item['product_id'])) {
                $cart_contains_add_on = true;
            } else {
                $cart_contains_base_product = true;
            }
        }

        if($cart_contains_add_on && !$cart_contains_base_product)
        {
            $text = __('Cannot proceed to checkout with an Add-on but no base product. Please add a base product to your cart.', WPCS_WAAS_HOST_TEXTDOMAIN);

            // Needs to be of type error, otherwise checkout is not prevented.
            \wc_add_notice($text, 'error');
        }
    }

    private static function is_product_addon($product_id)
    {
        $product_categories = wp_get_post_terms($product_id, self::TERM_TAX_SLUG);
        foreach ($product_categories as $category)
        {
            if($category->slug === self::TERM_SLUG)
            {
                return true;
            }
        }

        return false;
    }
}
