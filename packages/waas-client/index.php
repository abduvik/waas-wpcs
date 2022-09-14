<?php

require_once 'vendor/autoload.php';
require_once(ABSPATH . '/wp-admin/includes/plugin.php');

/*
Plugin Name: WaaS Client
Plugin URI: https://github.com/abduvik/wpcs-waas
Description: This plugin is used to handle secure communication between tenant on WPCS and Storefront.
Author: Abdu Tawfik
Version: 1.0.0
Author URI: https://www.abdu.dev
*/

use WaaSClient\Api\RolesController;
use WaaSClient\Api\SingleSignOnController;
use WaaSClient\Core\DecryptionService;
use WaaSClient\Core\HttpService;
use WaaSClient\Features\AdminRolesSettings;
use WaaSClient\Features\PluginBootstrap;
use WaaSClient\Features\RolesManager;
use WaaSClient\Features\SecureHostConnectionManager;
use WaaSClient\Features\AdminTenantSettings;


define("WAAS_PLUGIN_DIR_URI", plugin_dir_url(__FILE__));
define("WAAS_PLUGIN_DIR", plugin_dir_path(__FILE__));

define('WAAS_MAIN_HOST_URL', get_option('waas_host_website_url'));
define('WAAS_HOST_PUBLIC_KEYS', get_option('tenant_public_key'));

// Controllers to list for APIs
$host_http_service = new HttpService(WAAS_MAIN_HOST_URL . '/wp-json/waas-host/v1');
$decryptionService = new DecryptionService();

// Plugin Boostrap
new PluginBootstrap($host_http_service);

new SingleSignOnController($decryptionService);
new RolesController($host_http_service);

// Managers to list for Events
new SecureHostConnectionManager($host_http_service);
new RolesManager();

// UI
new AdminTenantSettings();
new AdminRolesSettings($host_http_service);
