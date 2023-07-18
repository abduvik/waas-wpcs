<?php

namespace WaaSHost\Features;

use WaaSHost\Api\SingleLogin;
use WaaSHost\Core\WPCSTenant;

class Notifications
{
    public static function init()
    {
        add_action('wpcs_tenant_ready', [__CLASS__, 'send_email_on_tenant_ready'], 10, 1);
    }

    public static function send_email_on_tenant_ready($subscription_id)
    {
        $should_send_email = apply_filters('wpcs_tenant_ready_email_allowed', true, $subscription_id);
        if (!$should_send_email) {
            return;
        }

        $order = new \WC_Order($subscription_id);

        $recipient = apply_filters('wpcs_tenant_ready_email_recipient', $order->get_billing_email(), $subscription_id);
        $subject = self::get_ready_email_subject($subscription_id, $recipient);
        $headers = apply_filters('wpcs_tenant_ready_email_headers', ['Content-Type: text/html; charset=UTF-8'], $subscription_id);
        $body = self::get_ready_email_body($subscription_id, $recipient);

        wp_mail($recipient, $subject, $body, $headers);
    }

    private static function get_ready_email_subject($subscription_id, $recipient)
    {
        $unfiltered_subject = apply_filters('wpcs_tenant_ready_email_subject', self::get_default_tenant_ready_subject(), $subscription_id);
        $replaceables = self::get_replaceables($subscription_id, $recipient);
        return self::do_replacements($unfiltered_subject, $replaceables);
    }

    public static function get_default_tenant_ready_subject()
    {
        return __('Your website is ready', WPCS_WAAS_HOST_TEXTDOMAIN);
    }

    private static function get_ready_email_body($subscription_id, $recipient)
    {
        $unfiltered_body = apply_filters('wpcs_tenant_ready_email_body', self::get_default_tenant_ready_body(), $subscription_id, $recipient);
        $replaceables = self::get_replaceables($subscription_id, $recipient);
        return self::do_replacements($unfiltered_body, $replaceables);
    }

    public static function get_default_tenant_ready_body()
    {
        return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html lang="en">
            <body>
                <p>Hello,</p>

                &nbsp;

                <p>Good news! Your website is ready for you!</p>
                <p>Use the link below to login to your website or visit it at <a href="{{ tenant_domain }}" target="_blank" rel="noopener">{{ tenant_domain }}</a>.</p>

                &nbsp;

                <p><a href="{{ tenant_one_click_login_url }}" target="_blank" rel="noopener"><strong>Login now</strong></a></p>
                <p><small>This login link will be active for {{ tenant_one_click_login_url_expires_after }} hours after this E-mail was sent.</small></p>

                &nbsp;

                <p>If the above link has expired, you can always find a fresh login link in your account at <a href="{{ storefront_url }}" target="_blank" rel="noopener">{{ storefront_name }}</a> under your <a href="{{ storefront_subscription_details_url }}" target="_blank" rel="noopener">subscription details</a>.</p>

                &nbsp;

                <p>Regards,</p>
                <p>The <a href="{{ storefront_url }}" target="_blank" rel="noopener">{{ storefront_name }}</a> team</p>
            </body>
        </html>';
    }

    public static function get_replaceables($subscription_id, $recipient)
    {
        $login_expiry_seconds = 172800;
        return apply_filters('wpcs_tenant_email_replaceables', [
            "tenant_domain" => "https://" . get_post_meta($subscription_id, WPCSTenant::WPCS_DOMAIN_NAME_META, true),
            "tenant_one_click_login_url" => SingleLogin::get_login_link($subscription_id, $recipient, $login_expiry_seconds),
            "tenant_one_click_login_url_expires_after" => $login_expiry_seconds / 3600,
            "storefront_name" => get_bloginfo('name'), 
            "storefront_url" => get_site_url(),
            "storefront_subscription_details_url" => apply_filters('wpcs_subscription_details_url', get_site_url(), $subscription_id),
        ], $subscription_id, $recipient);
    }

    public static function do_replacements($text, $replaceables)
    {
        $filtered_body = $text;
        foreach ($replaceables as $key => $value)
        {
            $filtered_body = str_replace([sprintf('{{ %s }}', $key), sprintf('{{%s}}', $key)], $value, $filtered_body);
        }

        return $filtered_body;
    }
}
