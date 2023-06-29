<?php

namespace WaaSHost\Features;

class AddonProductCategory
{
    public const TERM_SLUG = 'add-on';
    public const TERM_TAX_SLUG = 'product_cat';

    public static function init()
    {
        add_action('woocommerce_after_register_taxonomy', [__CLASS__, 'insert_product_category']);
        add_filter('woocommerce_add_to_cart_validation', [__CLASS__, 'filter_wc_add_to_cart_validation'], 10, 2 );
        add_action('woocommerce_check_cart_items', [__CLASS__, 'validate_all_cart_contents']);
    }

    public static function insert_product_category()
    {
        // Term already exists, abort early.
        if(term_exists(self::TERM_SLUG, self::TERM_TAX_SLUG))
        {
            return;
        }

        wp_insert_term(__('Add-on', WPCS_SAAS_HOST_TEXTDOMAIN), self::TERM_TAX_SLUG, [
            'slug' => self::TERM_SLUG,
            'description' => __( 'An add-on is used to sell additional features for the base plans you offer.', WPCS_SAAS_HOST_TEXTDOMAIN ),
        ]);
    }

    public static function filter_wc_add_to_cart_validation($passed_validation, $product_id)
    {
        $product = \wc_get_product($product_id);
        if(is_a($product, 'WC_Product'))
        {
            // If we're adding an add-on, there should already be a not-add-on in the cart
            if(self::is_product_addon($product_id))
            {
                global $woocommerce;
                $items = $woocommerce->cart->get_cart();

                // There needs to be at least one non-add-on in the cart
                foreach ($items as $item)
                {
                    if(!self::is_product_addon($item->product_id))
                    {
                        return $passed_validation;
                    }
                }

                // No base products were found, cannot add.
                \wc_add_notice(__('Cannot add an Add-on to the cart without a base product.', WPCS_SAAS_HOST_TEXTDOMAIN), 'notice');
                return false;
            }
        }

        return $passed_validation;
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
            $text = __('Cannot proceed to checkout with an Add-on but no base product. Please add a base product to your cart.', WPCS_SAAS_HOST_TEXTDOMAIN);

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
