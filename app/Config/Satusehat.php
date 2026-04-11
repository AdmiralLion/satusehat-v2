<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Satusehat extends BaseConfig
{
    public string $authUrl;
    public string $apiUrl;
    public string $clientId;
    public string $clientSecret;
    public string $organizationId;

    public function __construct()
    {
        parent::__construct();

        $this->authUrl         = env('satusehat.auth_url', '');
        $this->apiUrl          = env('satusehat.api_url', '');
        $this->clientId        = env('satusehat.client_id', '');
        $this->clientSecret    = env('satusehat.client_secret', '');
        $this->organizationId  = env('satusehat.organization_id', '');
    }
}
