<?php

namespace MondayCloneHost\Features;

class PluginBootstrap
{
    const ROLES_WP_OPTION = 'monday-host-roles-options';
    const API_V1_NAMESPACE = 'monday-host/v1';

    public function __construct()
    {
        add_action('woocommerce_product_query', [$this, 'hide_addon_products_from_shop_page']);
        add_filter('ssd_add_product_link', [$this, 'rename_add_new_product_to_add_new_subscription']);
        add_filter('ssd_product_query_args', [$this, 'only_show_addons_when_adding_ons'], 10, 1);
        add_filter('wcs_can_item_be_removed', [$this, 'only_addons_can_be_removed_from_subscription'], 10, 2);
        add_filter('http_request_timeout', [$this, 'increase_curl_timeout'], 10, 1);
    }

    function hide_addon_products_from_shop_page($q)
    {

        if (!is_shop()) {
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

    public function rename_add_new_product_to_add_new_subscription($link)
    {
        return str_replace('Add new product', 'Add new add-on', $link);
    }

    public function only_show_addons_when_adding_ons($args)
    {
        $args['tax_query'][] = [
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => 'add-on',
        ];

        return $args;
    }

    public function only_addons_can_be_removed_from_subscription(bool $can_show, \WC_Order_Item_Product $item)
    {

        $product_categories = wc_get_product_terms($item->get_product_id(), 'product_cat');
        foreach ($product_categories as $product_category) {
            if ($product_category->slug !== 'add-on') {
                return false;
            }
        }
        return $can_show;
    }

    public function increase_curl_timeout($timeout): int
    {
        return 20;
    }
}
