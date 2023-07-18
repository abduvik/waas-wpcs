<?php

use WaaSHost\Api\RolesController;
use WaaSHost\Api\SingleLogin;
use WaaSHost\Core\EncryptionService;
use WaaSHost\Core\HttpService;
use WaaSHost\Core\WPCSService;
use WaaSHost\Features\PluginBootstrap;
use WaaSHost\Features\TenantsAddOnSubscriptionManager;
use WaaSHost\Features\TenantsSubscriptionManager;
use WaaSHost\Features\UserAccountSubscriptionsSettings;
use WaaSHost\Features\UserWcTenantsCheckout;
use WaaSHost\Features\AdminWpcsSettings;
use WaaSHost\Features\AdminWpcsHome;
use WaaSHost\Features\AdminNotices;
use WaaSHost\Features\AddonProductCategory;
use WaaSHost\Features\Notifications;
use WaaSHost\Features\WooCommerceCartValidator;
use WaaSHost\Features\WooCommerceProductData;
use WaaSHost\Features\WPCSTenantStatusService;
use WaaSHost\Integrations\WoocommerceSubscriptionsIntegration;
use WaaSHost\Integrations\SubscriptionsForWoocommerceIntegration;

require_once 'vendor/autoload.php';

/**
 * @package WaaSHost
 * @version 2.0.5
 */
/*
Plugin Name: WaaS Host
Plugin URI: https://github.com/Daxez/waas-wpcs
Description: This plugin is used to create tenants on WPCS.io with support of WordPress, WooCommerce, WooCommerce Subscriptions and Self-service Dashboard for WooCommerce Subscriptions.
Author: WPCS
Version: 2.0.5
Author URI: https://wpcs.io
Update URI: wpcs-waas-host
*/

define( 'WPCS_WAAS_HOST_SLUG', 'wpcs-waas-host' );
define( 'WPCS_WAAS_HOST_BASENAME', plugin_basename( __FILE__ ) );
define( 'WPCS_WAAS_HOST_UPDATE_URI', 'wpcs-waas-host' );
define( 'WPCS_WAAS_HOST_VERSION', '2.0.5' );
define( 'WPCS_WAAS_HOST_TEXTDOMAIN', 'wpcs-waas-host-textdomain' );

define('WPCS_API_REGION', get_option('wpcs_credentials_region_setting')); // Or eu1, depending on your region.
define('WPCS_API_KEY', get_option('wpcs_credentials_api_key_setting')); // The API Key you retrieved from the console
define('WPCS_API_SECRET', get_option('wpcs_credentials_api_secret_setting')); // The API Secret you retrieved from the console

// Controllers to list for APIs
$wpcs_http_service = new HttpService('https://api.' . WPCS_API_REGION . '.wpcs.io', WPCS_API_KEY . ":" . WPCS_API_SECRET);
$wpcsService = new WPCSService($wpcs_http_service);
new RolesController($wpcsService);

// Managers to list for Events

// UI
SingleLogin::init();
new TenantsSubscriptionManager($wpcsService);
new TenantsAddOnSubscriptionManager($wpcsService);
new AdminWpcsSettings($wpcsService);
new UserAccountSubscriptionsSettings($wpcsService);
new UserWcTenantsCheckout($wpcsService);
new WPCSTenantStatusService($wpcsService);
AdminNotices::init();
AdminWpcsHome::init();
new WooCommerceProductData($wpcsService);
new WooCommerceCartValidator();
WaaSHost\Assets\Module::init();

// Integrations
SubscriptionsForWoocommerceIntegration::init();
WoocommerceSubscriptionsIntegration::init();

WaaSHost\Updater\Module::init();
new WaaSHost\Migrations\Module($wpcsService);
AddonProductCategory::init();
Notifications::init();

// Plugin Bootstrap
new PluginBootstrap();
