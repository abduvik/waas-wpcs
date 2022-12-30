<?php

namespace WaaSClient\Api;

use WaaSClient\Core\DecryptionService;
use WaaSClient\Features\SecureHostConnectionManager;
use WaaSClient\Features\PluginBootstrap;
use WP_Error;
use WP_REST_Request;

class SingleSignOnController
{
    private DecryptionService $decryptionService;
    private SecureHostConnectionManager $secureHostConnectionManager;

    public function __construct(DecryptionService $decryptionService, SecureHostConnectionManager $secureHostConnectionManager)
    {
        $this->decryptionService = $decryptionService;
        $this->secureHostConnectionManager = $secureHostConnectionManager;

        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }

    public function register_rest_routes()
    {
        register_rest_route(PluginBootstrap::API_V1_NAMESPACE, '/single_login/verify', array(
            'methods' => 'GET',
            'callback' => [$this, 'verify_single_login'],
        ));
    }

    public function verify_single_login(WP_REST_Request $request)
    {
        $token_encoded = urlencode($request->get_param('token'));
        $token_decoded = base64_decode(urldecode($token_encoded));
        $public_key = WAAS_HOST_PUBLIC_KEYS;

        if (empty($public_key) || strpos($public_key, '-----BEGIN PUBLIC KEY-----') !== 0) {
            $externalId = get_option(PluginBootstrap::EXTERNAL_ID);
            $this->secureHostConnectionManager->get_tenant_public_id($externalId);
            $public_key = get_option(SecureHostConnectionManager::TENANT_PUBLIC_KEY);

            if (empty($public_key) || strpos($public_key, '-----BEGIN PUBLIC KEY-----') !== 0) {
                return new WP_Error('Could not fetch Correct Public key from Storefront');
            }
        }

        $data = $this->decryptionService->decrypt($public_key, $token_decoded);

        if (!$data) {
            return new WP_Error('Decryption failed');
        }

        $data = json_decode($data);
        $user_email = $data->email;
        $user = get_user_by('email', $user_email);

        if (!$user) {
            return new WP_Error('User not found');
        }

        wp_clear_auth_cookie();
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID);
        do_action('wp_login', $user->user_login, $user);
        wp_redirect(admin_url());
        exit();
    }
}
