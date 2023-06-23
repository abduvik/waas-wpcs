<?php

use WaaSHost\Api\RolesController;
use WaaSHost\Api\SingleLogin;
use WaaSHost\Api\TenantsAuthKeys;
use WaaSHost\Core\EncryptionService;
use WaaSHost\Core\HttpService;
use WaaSHost\Core\WPCSService;
use WaaSHost\Features\PluginBootstrap;
use WaaSHost\Features\TenantsAddOnSubscriptionManager;
use WaaSHost\Features\TenantsSubscriptionManger;
use WaaSHost\Features\UserAccountSubscriptionsSettings;
use WaaSHost\Features\UserWcTenantsCheckout;
use WaaSHost\Features\AdminWcProductRole;
use WaaSHost\Features\AdminWpcsSettings;

require_once 'vendor/autoload.php';

/**
 * @package WaaSHost
 * @version 1.5.1
 */
/*
Plugin Name: WaaS Host
Plugin URI: https://github.com/Daxez/waas-wpcs
Description: This plugin is used to create tenants on WPCS.io with support of WordPress, WooCommerce, WooCommerce Subscriptions and Self-service Dashboard for WooCommerce Subscriptions. Forked from https://github.com/abduvik/wpcs-waas
Author: WPCS
Version: 1.5.1
Author URI: https://wpcs.io
*/

define('WPCS_API_REGION', get_option('wpcs_credentials_region_setting')); // Or eu1, depending on your region.
define('WPCS_API_KEY', get_option('wpcs_credentials_api_key_setting')); // The API Key you retrieved from the console
define('WPCS_API_SECRET', get_option('wpcs_credentials_api_secret_setting')); // The API Secret you retrieved from the console


// Controllers to list for APIs
$wpcs_http_service = new HttpService('https://api.' . WPCS_API_REGION . '.wpcs.io', WPCS_API_KEY . ":" . WPCS_API_SECRET);
$wpcsService = new WPCSService($wpcs_http_service);
$encryptionService = new EncryptionService();
new RolesController($wpcsService);

// Managers to list for Events

// UI
new TenantsAuthKeys();
new SingleLogin($encryptionService);
new TenantsSubscriptionManger($wpcsService, $encryptionService);
new TenantsAddOnSubscriptionManager();
new AdminWpcsSettings();
new UserAccountSubscriptionsSettings($wpcsService);
new AdminWcProductRole($wpcsService);
new UserWcTenantsCheckout($wpcsService);

// Plugin Bootstrap
new PluginBootstrap();