<?php

namespace WaaSHost\Features;

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
        $subject = apply_filters('wpcs_tenant_ready_email_subject', __('Your website is being created', WPCS_WAAS_HOST_TEXTDOMAIN), $subscription_id);
        $headers = apply_filters('wpcs_tenant_ready_email_headers', ['Content-Type: text/html; charset=UTF-8'], $subscription_id);
        $body = apply_filters('wpcs_tenant_ready_email_body', self::get_ready_email_body($subscription_id, $recipient), $subscription_id);

        wp_mail($recipient, $subject, $body, $headers);
    }

    private static function get_ready_email_body($subscription_id, $recipient)
    {
        $domain = get_post_meta($subscription_id, WPCSTenant::WPCS_DOMAIN_NAME_META, true);
        $storefront_login_url = apply_filters('wpcs_subscription_details_url',get_site_url(), $subscription_id);

        $text = '
        <!doctype html>
        <html lang=\'en\'>
        <body>
            <p>Hello,</p>
            <p>Good news!</p>
            <p>You can login to your website <a href="' . $domain . '">' . $domain . '</a>! Log into our shop, view your subscription and hit the "Login as ' . $recipient . '" button or link.</p>
            <p><strong>Your subscription can be found at</strong>: <a href="' . $storefront_login_url . '">' . $storefront_login_url . '</a></p>
        </body>
        </html>
        ';

        return $text;
    }
}
