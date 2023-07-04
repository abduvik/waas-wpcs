<?php

namespace WaaSHost\Core;

class WPCSTenant
{
    public const WPCS_WEBSITE_NAME_META = 'WPCS_WEBSITE_NAME_META';
    public const WPCS_DOMAIN_NAME_META = 'WPCS_DOMAIN_NAME_META';
    public const WPCS_BASE_DOMAIN_NAME_META = 'WPCS_BASE_DOMAIN_NAME_META';
    public const WPCS_SUBSCRIPTION_USER_ROLES = 'WPCS_SUBSCRIPTION_USER_ROLES';
    public const WPCS_TENANT_EXTERNAL_ID_META = 'WPCS_TENANT_EXTERNAL_ID_META';
    public const WPCS_TENANT_PUBLIC_KEY_META = 'WPCS_TENANT_PUBLIC_KEY_META';
    public const WPCS_TENANT_PRIVATE_KEY_META = 'WPCS_TENANT_PRIVATE_KEY_META';

    public const WPCS_TENANT_STATE = 'WPCS_TENANT_STATE';
    public const PROVISIONING = 'Provisioning';
    public const LINKING_DOMAIN = 'Linking Domain';
    public const REQUESTING_SSL = 'Requesting SSL';
    public const READY = 'Ready';
    public const DELETED = 'Deleted';

    private $post_id;

    public function __construct($post_id)
    {
        $this->post_id = $post_id;
    }

    public function get_status()
    {
        return get_post_meta($this->post_id, self::WPCS_TENANT_STATE, true);
    }

    public function update_status($status)
    {
        return update_post_meta($this->post_id, self::WPCS_TENANT_STATE, $status);
    }
}
