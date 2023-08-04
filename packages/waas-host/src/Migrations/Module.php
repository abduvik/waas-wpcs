<?php

namespace WaaSHost\Migrations;

use WaaSHost\Core\WPCSService;
use WaaSHost\Core\WPCSProduct;

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class Module
{
    const WPCS_PLUGIN_VERSION_OPTION = 'wpcs-plugin-version';

    private WPCSService $wpcsService;
    public function __construct(WPCSService $wpcsService)
    {
        $this->wpcsService = $wpcsService;

        if ($this->get_db_version() !== WPCS_WAAS_HOST_VERSION) {
            $this->migrate_non_wpcs_products();
            $this->update_api_reachability();
            $this->update_version();
        }
    }

    public function get_db_version()
    {
        return get_option(self::WPCS_PLUGIN_VERSION_OPTION, '0.0.0');
    }

    public function update_version()
    {
        update_option(self::WPCS_PLUGIN_VERSION_OPTION, WPCS_WAAS_HOST_VERSION);
    }

    public function migrate_non_wpcs_products()
    {
        $products = get_posts([
            'numberposts' => -1,
            'post_type' => 'product',
            'meta_query' => [
                [
                    'key' => WPCSProduct::WPCS_PRODUCT_ROLE_META,
                    'compare' => 'EXISTS',
                ]
            ],
        ]);

        foreach ($products as $product) {
            $wpcs_product = new WPCSProduct($product->ID);
            $wpcs_product->store_is_wpcs_product(true);
        }
    }

    public function update_api_reachability()
    {
        $can_reach = false;
        try {
            $this->wpcsService->test_reachability();
            $can_reach = true;
        } catch (\Exception $e) {
        }

        $this->wpcsService->update_is_reachable($can_reach);
    }
}
