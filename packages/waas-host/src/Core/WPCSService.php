<?php

namespace WaaSHost\Core;

use Exception;

class InvalidDomainException extends Exception
{
}

class WPCSService
{
    private HttpService $httpService;

    public function __construct(HttpService $httpService)
    {
        $this->httpService = $httpService;
    }

    /**
     * @throws \Exception
     */
    public function get_production_version()
    {
        $versions = $this->httpService->get('/v1/versions');

        foreach ($versions as $version) {
            if ($version->isProduction === true) {
                return $version;
            }
        }

        throw new \Exception('Failed to find any version');
    }

    /**
     * @throws \Exception
     */
    public function get_available_groupnames()
    {
        $snapshots= $this->httpService->get('/v1/snapshots?onlyProductionVersion=true');
        $a = array();
        foreach ($snapshots as $snapshot) {
            if (isset($snapshot->groupName) && !empty($snapshot->groupName) && !in_array($snapshot->groupName, $a)) {
                array_push($a, $snapshot->groupName);
            }
        }

        return $a;
    }

    public function create_tenant($args)
    {
        $payload = [
            'name' => $args['name'],
            'wordPressUserName' => $args['wordpress_username'],
            'wordPressUserEmail' => $args['wordpress_email'],
            'wordPressUserPassword' => $args['wordpress_password'],
            'wordPressUserRole' => $args['wordpress_user_role'],
        ];

        if (isset($args['custom_domain_name'])) {
            $payload['customDomainName'] = $args['custom_domain_name'];
        }

        if (isset($args['group_name'])) {
            $payload['groupName'] = $args['group_name'];
        }

        if (isset($args['php_constants'])) {
            $payload['phpConstants'] = $args['php_constants'];
        }

        apply_filters('wpcs_tenant_create_payload', $payload);

        return $this->httpService->post('/v1/tenants', $payload);
    }

    public function delete_tenant($args)
    {
        return $this->httpService->delete('/v1/tenants?tenantId=' . $args['external_id']);
    }

    public function add_tenant_domain($args)
    {
        $this->httpService->post('/v1/tenants/domains?externalId=' . $args['external_id'], [
            'setAsMainDomain' => true,
            'domainName' => $args['domain_name'],
        ]);
    }

    public function delete_tenant_domain($args)
    {
        $url = '/v1/tenants/domains?externalId=' . $args['external_id'] . "&domainName=" . $args['old_domain_name'];
        $this->httpService->delete($url);
    }

    public function domain_available($domain)
    {
        $url = '/v1/tenants/domains/available?domain=' . $domain;
        $response = $this->httpService->getraw($url);
        if (is_wp_error($response)) {
            throw new Exception("Something went wrong connecting to the API");
        }


        if (wp_remote_retrieve_response_code($response) == 400) {
            throw new InvalidDomainException("Domain was not valid!");
        }

        return json_decode($response['body'])->available;
    }
}
