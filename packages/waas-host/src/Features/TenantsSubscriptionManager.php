<?php

namespace WaaSHost\Features;

use Exception;
use WaaSHost\Core\EncryptionService;
use WaaSHost\Core\WPCSService;
use WaaSHost\Core\WPCSTenant;
use WC_Order;

class TenantsSubscriptionManager
{
    private WPCSService $wpcsService;
    private EncryptionService $encryptionService;

    public function __construct(WPCSService $wpcsService, EncryptionService $encryptionService)
    {
        $this->wpcsService = $wpcsService;
        $this->encryptionService = $encryptionService;

        add_action('wpcs_subscription_created', [$this, 'create_tenant_when_subscription_created'], 10, 2);

        add_action('wpcs_subscription_expired', [$this, 'remove_tenant_when_subscription_expired']);
    }

    /**
     * @throws Exception
     */
    public function create_tenant_when_subscription_created($subscription_id, WC_Order $order)
    {
        $order_items = $order->get_items();

        $group_name = "";
        $subscription_roles = [];
        foreach ($order_items as $key => $item)
        {
            $product_role = get_post_meta($item->get_product_id(), WPCSTenant::WPCS_PRODUCT_ROLE_META, true);
            if (!empty($product_role)) {
                $subscription_roles[] = $product_role;
            }
            $group_name = empty($group_name) ? get_post_meta($item->get_product_id(), WPCSTenant::WPCS_PRODUCT_GROUPNAME_META, true) : $group_name;
        }

        $website_name = sanitize_text_field(get_post_meta($order->get_id(), WPCSTenant::WPCS_WEBSITE_NAME_META, true));
        $password = wp_generate_password();

        $keys = $this->encryptionService->generate_key_pair();

        $args = [
            'name' => $website_name,
            'wordpress_username' => str_replace('-', '_', sanitize_title_with_dashes(remove_accents($order->get_formatted_billing_full_name()))),
            'wordpress_email' => $order->get_billing_email(),
            'wordpress_password' => $password,
            'wordpress_user_role' => 'administrator',
            'group_name' => ($group_name === false || empty($group_name)) ? null : $group_name,
            'php_constants' => [
                'WPCS_TENANT_ROLES' => ['value' => implode(",", $subscription_roles), 'isPrivate' => false],
                'WPCS_PUBLIC_KEY' => ['value' => base64_encode($keys['public_key']), 'isPrivate' => false],
            ]
        ];

        $tenant_root_domain = get_option('wpcs_host_settings_root_domain', '');
        if ($tenant_root_domain !== '')
        {
            $subdomain = sanitize_title_with_dashes(remove_accents($website_name));
            $args['custom_domain_name'] = $subdomain . '.' . $tenant_root_domain;
        }

        $new_tenant = $this->wpcsService->create_tenant($args);

        $domain_name = $new_tenant->customDomain ?? $new_tenant->baseDomain;

        update_post_meta($subscription_id, WPCSTenant::WPCS_TENANT_EXTERNAL_ID_META, $new_tenant->externalId);
        update_post_meta($subscription_id, WPCSTenant::WPCS_TENANT_PRIVATE_KEY_META, $keys['private_key']);
        update_post_meta($subscription_id, WPCSTenant::WPCS_DOMAIN_NAME_META, $domain_name);
        update_post_meta($subscription_id, WPCSTenant::WPCS_BASE_DOMAIN_NAME_META, $new_tenant->baseDomain);
        update_post_meta($subscription_id, WPCSTenant::WPCS_SUBSCRIPTION_USER_ROLES, [$product_role]);

        $this->send_created_email([
            'email' => $order->get_billing_email(),
            'password' => $password,
            'domain' => $domain_name
        ]);
    }

    public function send_created_email($args)
    {
        wp_mail($args['email'], 'Your website is being created', "
        <!doctype html>
        <html lang='en'>
        <body>
            <p>Hello,</p>
            <p>You can login to your website in a few minutes. Use the info below to login. Don't forget to reset your password!</p>
            <p><strong>Admin Url</strong>: <a href='https://{$args['domain']}/wp-admin'>https://{$args['domain']}/wp-admin</a></p>
            <p><strong>Email</strong> : {$args['email']}</p>
            <p><strong>Password</strong> : {$args['password']}</p>
        </body>
        </html>
        ", ['Content-Type: text/html; charset=UTF-8']);
    }

    public function remove_tenant_when_subscription_expired($subscription_id)
    {
        $tenant_external_id = get_post_meta($subscription_id, WPCSTenant::WPCS_TENANT_EXTERNAL_ID_META, true);
        error_log($tenant_external_id);
        $this->wpcsService->delete_tenant([
            'external_id' => $tenant_external_id
        ]);
    }
}
