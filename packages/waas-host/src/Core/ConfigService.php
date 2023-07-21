<?php

namespace WaaSHost\Core;

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
    exit;

class ConfigService
{
    public function get_api_key()
    {
        $value = defined('WPCS_API_KEY') ? WPCS_API_KEY : get_option('wpcs_credentials_api_key_setting');
        return apply_filters('wpcs_settings_api_key', $value);
    }

    public function get_api_secret()
    {
        $value = defined('WPCS_API_SECRET') ? WPCS_API_SECRET : get_option('wpcs_credentials_api_secret_setting');
        return apply_filters('wpcs_settings_api_secret', $value);
    }

    public function get_api_region()
    {
        $value = defined('WPCS_API_REGION') ? WPCS_API_REGION : get_option('wpcs_credentials_region_setting');
        return apply_filters('wpcs_settings_api_region', $value);
    }

    public function check_credentials()
    {
        $region_exists = $this->get_api_region() !== false;
        $key_exists = $this->get_api_key() !== false;
        $secret_exists = $this->get_api_secret() !== false;
        return $region_exists && $key_exists && $secret_exists;
    }
}
