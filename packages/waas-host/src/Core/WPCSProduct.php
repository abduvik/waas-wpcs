<?php

namespace WaaSHost\Core;

class WPCSProduct
{
    public const IS_WPCS_PRODUCT_META = 'is_wpcs_product';
    public const WPCS_PRODUCT_TYPE_META = 'wpcs_product_type';
    public const WPCS_PRODUCT_TYPE_BASE_PRODUCT = 'base_product';
    public const WPCS_PRODUCT_TYPE_ADDON = 'addon';

    public const WPCS_PRODUCT_ROLE_META = 'WPCS_PRODUCT_ROLE_META';
    public const WPCS_PRODUCT_GROUPNAME_META = 'WPCS_PRODUCT_GROUPNAME_META';

    private $post_id;

    public function __construct($post_id)
    {
        $this->post_id = $post_id;
    }

    public function store_is_wpcs_product($true_or_false)
    {
        update_post_meta(
            $this->post_id,
            '_' . self::IS_WPCS_PRODUCT_META,
            $true_or_false ? 'yes' : 'no',
        );
    }

    public function is_wpcs_product()
    {
        return get_post_meta(
            $this->post_id,
            '_' . self::IS_WPCS_PRODUCT_META,
            true,
        );
    }
}
