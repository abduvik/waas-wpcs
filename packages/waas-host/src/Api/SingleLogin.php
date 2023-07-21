<?php

namespace WaaSHost\Api;

use WaaSHost\Features\PluginBootstrap;
use WaaSHost\Core\EncryptionService;
use WaaSHost\Core\WPCSTenant;
use WP_REST_Request;

class SingleLogin
{
    public static function init()
    {
        add_action('rest_api_init', [__CLASS__, 'register_rest_routes']);
    }

    public static function register_rest_routes()
    {
        register_rest_route(PluginBootstrap::API_V1_NAMESPACE, '/tenant/single_login', array(
            'methods' => 'GET',
            'callback' => [__CLASS__, 'handle_api_one_click_login'],
            'permission_callback' => [__CLASS__, 'guard_generate_single_login_link']
        ));
    }

    public static function guard_generate_single_login_link(WP_REST_Request $request)
    {
        $subscription_id = sanitize_text_field($request->get_param('subscription_id'));
        $login_email = $request->get_param('email');
        $order_email = apply_filters('wpcs_subscription_id_email_for_login_guard', '', $subscription_id);

        return $login_email === $order_email;
    }

    public static function get_login_link($subscription_id, $email, $expiry_delay_in_seconds)
    {
        $domain = get_post_meta($subscription_id, WPCSTenant::WPCS_DOMAIN_NAME_META, true);
        $base_domain = get_post_meta($subscription_id, WPCSTenant::WPCS_BASE_DOMAIN_NAME_META, true);

        $domain = $domain ?: $base_domain;

        $private_key = get_post_meta($subscription_id, WPCSTenant::WPCS_TENANT_PRIVATE_KEY_META, true);

        $login_data = [
            'email' => $email,
            'purpose' => 'login',
            'expires' => gmdate("U") + $expiry_delay_in_seconds, // Add half an hour in seconds
        ];

        $token = EncryptionService::encrypt($private_key, json_encode($login_data));
        $token_encoded = urlencode(base64_encode($token));

        return 'https://' . $domain . "/wp-json/waas-client/v1/single_login/verify?token=" . $token_encoded;
    }

    public static function handle_api_one_click_login(WP_REST_Request $request)
    {
        $subscription_id = $request->get_param('subscription_id');
        $email = $request->get_param('email');

        wp_redirect(self::get_login_link($subscription_id, $email, 1800));
        exit();
    }
}
