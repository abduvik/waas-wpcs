<?php

namespace WaaSHost\Features;

class PluginBootstrap
{
    const ROLES_WP_OPTION = 'waas-host-roles-options';
    const API_V1_NAMESPACE = 'waas-host/v1';

    public function __construct()
    {
        add_action('woocommerce_product_query', [$this, 'hide_addon_products_from_shop_page']);
        add_filter('wcs_can_item_be_removed', [$this, 'only_addons_can_be_removed_from_subscription'], 10, 2);
    }

    function hide_addon_products_from_shop_page($q)
    {
        if (!\is_shop()) {
            return;
        }

        $tax_query = (array)$q->get('tax_query');

        $tax_query[] = [
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => 'add-on',
            'operator' => 'NOT IN'
        ];

        $q->set('tax_query', $tax_query);
    }

    public function only_addons_can_be_removed_from_subscription(bool $can_remove, \WC_Order_Item_Product $item)
    {
        // Default is true, so if somebody else decided this cannot be removed, honor that.
        if(!$can_remove) {
            return $can_remove;
        }

        $product_categories = wc_get_product_terms($item->get_product_id(), 'product_cat');

        $is_addon = false;
        foreach ($product_categories as $product_category)
        {
            if ($product_category->slug === 'add-on')
            {
                $is_addon = true;
            }
        }

        return $is_addon;
    }
}
