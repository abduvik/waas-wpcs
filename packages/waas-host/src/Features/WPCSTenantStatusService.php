<?php

namespace WaaSHost\Features;

use WaaSHost\Core\WPCSService;
use WaaSHost\Core\WPCSTenant;

class WPCSTenantStatusService
{
    public const PROVISIONING = 'Provisioning';
    public const LINKING_DOMAIN = 'Linking Domain';
    public const REQUESTING_SSL = 'Requesting SSL';
    public const READY = 'Ready';
    public const DELETED = 'Deleted';
    
    private const WPCS_TENANT_STATE = 'WPCS_TENANT_STATE';
    private WPCSService $wpcsService;

    public function __construct(WPCSService $wpcsService)
    {
        $this->wpcsService = $wpcsService;

        add_filter('cron_schedules', [$this, 'add_cron_schedules']);

        if (!wp_next_scheduled('wpcs_cron_poll_tenant_status')) {
            wp_schedule_event(time(), 'five_seconds', 'wpcs_cron_poll_tenant_status');
        }

        add_action('wpcs_tenant_linked_to_subscription', [$this, 'start_poll_tenant_status']);
        add_action('wpcs_subscription_removed', [$this, 'set_tenant_deleted']);
        add_action('wpcs_cron_poll_tenant_status', [$this, 'poll_tenant_status']);
    }

    public function add_cron_schedules($schedules)
    {
        $schedules['five_seconds'] = [
            'interval' => 5,
            'display'  => esc_html__( 'Every Five Seconds' ),
        ];

        $schedules['every_minute'] = [
            'interval' => 60,
            'display'  => esc_html__( 'Every Minute' ),
        ];

        return $schedules;
    }

    public function start_poll_tenant_status($subscription_id)
    {
        update_post_meta($subscription_id, self::WPCS_TENANT_STATE, self::PROVISIONING);
    }

    public function set_tenant_deleted($subscription_id)
    {
        update_post_meta($subscription_id, self::WPCS_TENANT_STATE, self::DELETED);
    }

    public function poll_tenant_status()
    {
        global $wpdb;

        $tbl = $wpdb->prefix . 'postmeta';
        $results = $wpdb->get_results("SELECT post_id as subscription_id, meta_value as current_status FROM $tbl m WHERE m.meta_key ='" . self::WPCS_TENANT_STATE . "' AND m.meta_value NOT IN (\"".self::READY."\",\"".self::DELETED."\")");

        foreach ($results as $result) {
            try {
                $subscription_id = $result->subscription_id;
                $current_status = $result->current_status;

                $external_id = get_post_meta($subscription_id, WPCSTenant::WPCS_TENANT_EXTERNAL_ID_META, true);
                $target_domain_name = get_post_meta($subscription_id, WPCSTenant::WPCS_DOMAIN_NAME_META, true);

                switch ($current_status) {
                    case self::PROVISIONING:
                        $tenant = $this->wpcsService->get_tenant_safe($external_id);
                        if (!isset($tenant)) {
                            error_log('Tenant not set');
                            break;
                        }

                        update_post_meta($subscription_id, self::WPCS_TENANT_STATE, self::LINKING_DOMAIN);
                    case self::LINKING_DOMAIN:
                        $tenant = $this->wpcsService->get_tenant_safe($external_id);

                        if (!isset($tenant) || $tenant->statusCode === 0 || $target_domain_name !== $tenant->domainName) {
                            break;
                        }

                        update_post_meta($subscription_id, self::WPCS_TENANT_STATE, self::REQUESTING_SSL);
                    case self::REQUESTING_SSL:
                        try {
                            $response = wp_remote_head("https://" . $target_domain_name, [
                                "redirection" => 0
                            ]);

                            $response_code = wp_remote_retrieve_response_code($response);

                            if ($response_code === 200) {
                                update_post_meta($subscription_id, self::WPCS_TENANT_STATE, self::READY);
                                do_action('wpcs_tenant_ready', $subscription_id, $external_id, $target_domain_name);
                            }
                        } catch (\Error $err) {
                            // Check error?
                        }
                        break;
                    default:
                        error_log("Subscription with Id " . $subscription_id . " has a weird " . self::WPCS_TENANT_STATE . " of " . $current_status);
                        break;
                }
            } catch (\Exception $e) {
            }
        }
    }
}
