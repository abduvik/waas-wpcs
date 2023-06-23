<?php

namespace WaaSHost\Features;

use Exception;
use WaaSHost\Core\EncryptionService;
use WaaSHost\Core\WPCSService;
use WaaSHost\Core\WPCSTenant;
use WC_Order;

class TenantsSubscriptionManger
{
    private WPCSService $wpcsService;
    private EncryptionService $encryptionService;

    public function __construct(WPCSService $wpcsService, EncryptionService $encryptionService)
    {
        $this->wpcsService = $wpcsService;
        $this->encryptionService = $encryptionService;

        add_action('woocommerce_checkout_subscription_created', [$this, 'create_tenant_when_subscription_created'], 10, 2);

        add_action('woocommerce_subscription_status_cancelled', [$this, 'remove_tenant_when_subscription_expired']);
    }

    /**
     * @throws Exception
     */
    public function create_tenant_when_subscription_created(\WC_Subscription $subscription, WC_Order $order)
    {
        $order_items = $order->get_items();
        $product = reset($order_items);
        $product_role = get_post_meta($product->get_product_id(), WPCSTenant::WPCS_PRODUCT_ROLE_META, true);
        $group_name = get_post_meta($product->get_product_id(), WPCSTenant::WPCS_PRODUCT_GROUPNAME_META, true);
        $website_name = sanitize_text_field(get_post_meta($order->get_id(), WPCSTenant::WPCS_WEBSITE_NAME_META, true));
        $password = wp_generate_password();


        $args = [
            'name' => $website_name,
            'wordpress_username' => str_replace('-', '_', sanitize_title_with_dashes(remove_accents($order->get_formatted_billing_full_name()))),
            'wordpress_email' => $order->get_billing_email(),
            'wordpress_password' => $password,
            'wordpress_user_role' => 'administrator',
            'group_name' => ($group_name === false || empty($group_name)) ? null : $group_name,
        ];

        $tenant_root_domain = get_option('wpcs_host_settings_root_domain', '');
        if ($tenant_root_domain !== '') {
            $subdomain = sanitize_title_with_dashes(remove_accents($website_name));
            $args['custom_domain_name'] = $subdomain . '.' . $tenant_root_domain;
        }

        $new_tenant = $this->wpcsService->create_tenant($args);

        $keys = $this->encryptionService->generate_key_pair();
        $domain_name = $new_tenant->customDomain ?? $new_tenant->baseDomain;

        update_post_meta($subscription->get_id(), WPCSTenant::WPCS_TENANT_EXTERNAL_ID_META, $new_tenant->externalId);
        update_post_meta($subscription->get_id(), WPCSTenant::WPCS_TENANT_PUBLIC_KEY_META, $keys['public_key']);
        update_post_meta($subscription->get_id(), WPCSTenant::WPCS_DOMAIN_NAME_META, $domain_name);
        update_post_meta($subscription->get_id(), WPCSTenant::WPCS_BASE_DOMAIN_NAME_META, $new_tenant->baseDomain);
        update_post_meta($subscription->get_id(), WPCSTenant::WPCS_TENANT_PRIVATE_KEY_META, $keys['private_key']);
        update_post_meta($subscription->get_id(), WPCSTenant::WPCS_SUBSCRIPTION_USER_ROLES, [$product_role]);

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

    public function remove_tenant_when_subscription_expired(\WC_Subscription $subscription)
    {
        $tenant_external_id = get_post_meta($subscription->get_id(), WPCSTenant::WPCS_TENANT_EXTERNAL_ID_META, true);
        error_log($tenant_external_id);
        $this->wpcsService->delete_tenant([
            'external_id' => $tenant_external_id
        ]);
    }
}
