<?php

namespace WaaSHost\Core;

use Exception;

class WPCSTenant
{
    public const WPCS_WEBSITE_NAME_META = 'WPCS_WEBSITE_NAME_META';
    public const WPCS_DOMAIN_NAME_META = 'WPCS_DOMAIN_NAME_META';
    public const WPCS_BASE_DOMAIN_NAME_META = 'WPCS_BASE_DOMAIN_NAME_META';
    public const WPCS_PRODUCT_ROLE_META = 'WPCS_PRODUCT_ROLE_META';
    public const WPCS_SUBSCRIPTION_USER_ROLES = 'WPCS_SUBSCRIPTION_USER_ROLES';
    public const WPCS_TENANT_EXTERNAL_ID_META = 'WPCS_TENANT_EXTERNAL_ID_META';
    public const WPCS_TENANT_PUBLIC_KEY_META = 'WPCS_TENANT_PUBLIC_KEY_META';
    public const WPCS_TENANT_PRIVATE_KEY_META = 'WPCS_TENANT_PRIVATE_KEY_META';

    private string $subscription_id;

    public function __construct(string $subscription_id)
    {
        $this->subscription_id = $subscription_id;
    }

    /**
     * @throws Exception
     */
    public static function from_wpcs_external_id(string $wpcs_external_id)
    {
        global $wpdb;

        $tbl = $wpdb->prefix . 'postmeta';
        $tbl2 = $wpdb->prefix . 'posts';
        $prepare_guery = $wpdb->prepare("SELECT post_id FROM $tbl m INNER JOIN $tbl2 p ON m.post_id = p.id WHERE p.post_type = 'shop_subscription' AND m.meta_key ='" . static::WPCS_TENANT_EXTERNAL_ID_META . "' AND m.meta_value = '%s'", $wpcs_external_id);
        $get_values = $wpdb->get_col($prepare_guery);

        // check results ##
        if (count($get_values) !== 1) {
            throw new Exception("not found");
        }

        $subscription_id = $get_values[0];

        return new WPCSTenant($subscription_id);
    }

    public function get_auth_keys(): array
    {
        $public_key = get_post_meta($this->subscription_id, static::WPCS_TENANT_PUBLIC_KEY_META, true);
        $private_key = get_post_meta($this->subscription_id, static::WPCS_TENANT_PRIVATE_KEY_META, true);

        return [
            'public_key' => $public_key,
            'private_key' => $private_key,
        ];
    }

    public function get_subscription_id()
    {
        return $this->subscription_id;
    }
}
