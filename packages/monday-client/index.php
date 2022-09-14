<?php

require_once 'vendor/autoload.php';
require_once(ABSPATH . '/wp-admin/includes/plugin.php');

/*
Plugin Name: Monday-Clone Client
Plugin URI: https://github.com/abduvik/wp-monday-clone
Description: This plugin is used to handle secure communication between tenant and Storefront.
Author: Abdu Tawfik
Version: 2.0.0
Author URI: https://www.abdu.dev
*/

use MondayCloneClient\Api\RolesController;
use MondayCloneClient\Api\SingleSignOnController;
use MondayCloneClient\Core\DecryptionService;
use MondayCloneClient\Core\HttpService;
use MondayCloneClient\Features\AdminRolesSettings;
use MondayCloneClient\Features\PluginBootstrap;
use MondayCloneClient\Features\RolesManager;
use MondayCloneClient\Features\SecureHostConnectionManager;
use MondayCloneClient\Features\AdminTenantSettings;


define("MONDAY_PLUGIN_DIR_URI", plugin_dir_url(__FILE__));
define("MONDAY_PLUGIN_DIR", plugin_dir_path(__FILE__));

define('MONDAY_MAIN_HOST_URL', get_option('monday_host_website_url'));
define('MONDAY_HOST_PUBLIC_KEYS', get_option('tenant_public_key'));

// Controllers to list for APIs
$host_http_service = new HttpService(MONDAY_MAIN_HOST_URL . '/wp-json/monday-host/v1');
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
