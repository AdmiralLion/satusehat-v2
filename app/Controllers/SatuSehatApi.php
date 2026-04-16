<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use GuzzleHttp\Client;
use Config\Satusehat;
use App\Models\M_Main;
use App\Models\M_Api;
use Ramsey\Uuid\Uuid;

class SatuSehatApi extends Controller
{
    protected Client $httpClient;
    protected Satusehat $config;
    protected ?string $token = null;
    protected $m_main;
    protected $m_api;
    use ResponseTrait;

    public function __construct()
    {
        $this->m_main = new M_Main();
        $this->m_api = new M_Api();
        $this->config     = config('Satusehat');
        $this->httpClient = new Client([
            'timeout' => 30,
            'verify'  => false,
            'allow_redirects' => ['strict' => true],
        ]);
    }

    /**
     * Get OAuth2 access token from Satu Sehat API.
     */
    public function getToken(): ResponseInterface
    {
        try {
            $response = $this->httpClient->post("{$this->config->authUrl}/token", [
                'form_params' => [
                    'client_id'     => $this->config->clientId,
                    'client_secret' => $this->config->clientSecret,
                    'grant_type'    => 'client_credentials',
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            // dd($body);

            if (isset($body['access_token'])) {
                $this->token = $body['access_token'];
                $respond = $this->respond([
                    'status' => 'success',
                    'token'  => $body['access_token'],
                    'expires_in' => $body['expires_in'] ?? null,
                ]);
                return $respond;
            }

            return $this->respond([
                'status'  => 'error',
                'message' => 'Access token not found in response',
                'raw'     => $body,
            ]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $response = $e->hasResponse() ? $e->getResponse() : null;
            return $this->respond([
                'status'  => 'error',
                'message' => $e->getMessage(),
                'body'    => $response ? json_decode($response->getBody()->getContents(), true) : null,
            ]);
        } catch (\Throwable $e) {
            return $this->respond([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Fetch and cache token internally for subsequent API calls.
     */
    protected function fetchToken(): ?array
    {
        
        try {
            $response = $this->httpClient->post("{$this->config->authUrl}/accesstoken?grant_type=client_credentials", [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept'       => 'application/json',
                ],
                'form_params' => [
                    'client_id'     => trim($this->config->clientId),
                    'client_secret' => trim($this->config->clientSecret),
                ],
            ]);
            

            $raw = (string) $response->getBody();
            $body = json_decode($raw, true);


            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'SatuSehat token JSON decode error: ' . json_last_error_msg() . ' | Raw: ' . $raw);
                return null;
            }

            if (isset($body['access_token'])) {
                $this->token = $body['access_token'];
                return [
                    'access_token' => $body['access_token'],
                    'expires_in'   => $body['expires_in'] ?? 3600,
                ];
            }

            log_message('error', 'SatuSehat token response missing access_token: ' . $raw);
            return null;
        } catch (\Throwable $e) {
            log_message('error', 'SatuSehat token fetch failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Ensure we have a valid token before making API calls.
     */
    protected function ensureToken(): bool
    {
        if ($this->token !== null) {
            return true;
        }
        $result = $this->fetchToken();
        return $result !== null && isset($result['access_token']);
    }

    /**
     * GET request to Satu Sehat FHIR API.
     */
    public function get(string $resourcePath, array $params = []): array
    {
        if (!$this->ensureToken()) {
            return ['status' => 'error', 'message' => 'Failed to obtain access token'];
        }

        try {
            $response = $this->httpClient->get("{$this->config->apiUrl}/{$resourcePath}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Accept'        => 'application/json',
                ],
                'query' => $params,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return $this->handleException($e);
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * POST request to Satu Sehat FHIR API (create resource).
     */
    public function post(string $resourcePath, array $payload): array
    {
        if (!$this->ensureToken()) {
            return ['status' => 'error', 'message' => 'Failed to obtain access token'];
        }

        try {
            $response = $this->httpClient->post("{$this->config->apiUrl}/{$resourcePath}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                ],
                'json' => $payload,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return $this->handleException($e);
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * PUT request to Satu Sehat FHIR API (update resource).
     */
    public function put(string $resourcePath, array $payload): array
    {
        if (!$this->ensureToken()) {
            return ['status' => 'error', 'message' => 'Failed to obtain access token'];
        }

        try {
            $response = $this->httpClient->put("{$this->config->apiUrl}/{$resourcePath}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                ],
                'json' => $payload,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return $this->handleException($e);
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Handle Guzzle RequestException and extract error details.
     */
    protected function handleException(\GuzzleHttp\Exception\RequestException $e): array
    {
        $response = $e->hasResponse() ? $e->getResponse() : null;
        $body     = $response ? json_decode($response->getBody()->getContents(), true) : null;
        $status   = $response ? $response->getStatusCode() : 0;

        log_message('error', 'SatuSehat API error: ' . $e->getMessage());

        return [
            'status'  => 'error',
            'code'    => $status,
            'message' => $e->getMessage(),
            'body'    => $body,
        ];
    }

    // -----------------------------------------------------------------------
    // HTTP endpoint wrappers (callable via routes for testing/debugging)
    // -----------------------------------------------------------------------

    /**
     * GET /satusehat-api/proxy?path=Encounter&id=xxx
     */
    public function proxyGet(): ResponseInterface
    {
        $path   = $this->request->getGet('path');
        $params = $this->request->getGet();
        unset($params['path']);

        $result = $this->get($path, $params);
        return $this->respond($result);
    }

    /**
     * POST /satusehat-api/proxy
     * Body: { "path": "Encounter", "payload": {...} }
     */
    public function proxyPost(): ResponseInterface
    {
        $input   = $this->request->getJSON(true);
        $path    = $input['path']    ?? '';
        $payload = $input['payload'] ?? [];

        $result = $this->post($path, $payload);
        return $this->respond($result);
    }

    /**
     * PUT /satusehat-api/proxy
     * Body: { "path": "Encounter/xxx", "payload": {...} }
     */
    public function proxyPut(): ResponseInterface
    {
        $input   = $this->request->getJSON(true);
        $path    = $input['path']    ?? '';
        $payload = $input['payload'] ?? [];

        $result = $this->put($path, $payload);
        return $this->respond($result);
    }

    public function kirim_data($payload, $namafunc, $user_act = null, $kunjungan_id = null){
        $data_token = $this->m_main->get_token();
        $now = time();
        $tgldb = strtotime($data_token['tgl_act']);
        $expired = $data_token['expires_in'];
        $token = $data_token['token'];
        $timediff = strtotime(date('Y-m-d H:i:s')) - $tgldb;
        
        if (!$token || $expired <= $timediff) {
            $new_token = $this->fetchToken();
            if (!$new_token) {
                $error = ['kode' => 500, 'pesan' => 'Gagal mengambil token dari Satu Sehat'];
                $this->m_main->save_log($user_act, $kunjungan_id, $error);
                return $error;
            }
            $token = $new_token['access_token'];
            $expires_at = $new_token['expires_in'];
            $this->m_main->save_token($token, $expires_at);
        }

        try {
            if ($payload !== null) {
                // POST request
                $url = $namafunc !== '' ? "{$this->config->apiUrl}/{$namafunc}" : $this->config->apiUrl;
                $response = $this->httpClient->post($url, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Accept'        => 'application/json',
                        'Content-Type'  => 'application/json',
                    ],
                    'json' => $payload,
                ]);
            } else {
                // GET request
                $url = $namafunc !== '' ? "{$this->config->apiUrl}/{$namafunc}" : $this->config->apiUrl;
                $response = $this->httpClient->get($url, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Accept'        => 'application/json',
                    ],
                ]);
            }

            $raw = (string) $response->getBody();
            $body = json_decode($raw, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $error = ['kode' => 500, 'pesan' => 'JSON decode error', 'raw' => $raw];
                $this->m_main->save_log($user_act, $kunjungan_id, $error);
                return $error;
            }

            // Log if response contains error (e.g. OperationOutcome)
            if (isset($body['issue']) || isset($body['kode'])) {
                $this->m_main->save_log($user_act, $kunjungan_id, $body);
            }

            return $body;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errResponse = $e->hasResponse() ? $e->getResponse() : null;
            $body = $errResponse ? json_decode((string) $errResponse->getBody(), true) : null;
            $error = ['kode' => $e->getCode() ?: 500, 'pesan' => $e->getMessage(), 'body' => $body];
            $this->m_main->save_log($user_act, $kunjungan_id, $error);
            return $error;
        } catch (\Throwable $e) {
            $error = ['kode' => 500, 'pesan' => $e->getMessage()];
            $this->m_main->save_log($user_act, $kunjungan_id, $error);
            return $error;
        }
    }

    public function get_ihs_pasien($pasien_id){
        $data_pas = $this->m_main->get_pasien($pasien_id);

        if (!$data_pas) {
            return ['kode' => 404, 'pesan' => 'Data Pasien Tidak Ditemukan'];
        }

        // Priority 1: search by NIK
        if (!empty($data_pas['no_ktp'])) {
            $nik = trim($data_pas['no_ktp']);
            $res = $this->kirim_data(null, "Patient?identifier=https://fhir.kemkes.go.id/id/nik|{$nik}");

            if (isset($res['entry'][0]['resource']['id'])) {
                return $res;
            }
        }

        // Priority 2: search by name + birthdate + gender
        if (!empty($data_pas['nama']) && !empty($data_pas['tgl_lahir']) && !empty($data_pas['sex'])) {
            $name     = urlencode(trim($data_pas['nama']));
            $dob      = date('Y-m-d', strtotime($data_pas['tgl_lahir']));
            $gender   = strtolower(trim($data_pas['sex'])) === 'perempuan' ? 'female' : 'male';
            
            $res = $this->kirim_data(null, "Patient?name={$name}&birthdate={$dob}&gender={$gender}");

            if (isset($res['entry'][0]['resource']['id'])) {
                return $res;
            }
        }

        return ['kode' => 404, 'pesan' => 'Data Pasien Tidak Lengkap, Segera lengkapi terlebih dahulu!'];
    }

    public function get_ihs_dokter($dokter_id){
        $data_dok = $this->m_main->get_dokter($dokter_id);

        if (!$data_dok) {
            return ['kode' => 404, 'pesan' => 'Data Dokter Tidak Ditemukan'];
        }

        if (!empty($data_dok['nik'])) {
            $nik = trim($data_dok['nik']);
            $res = $this->kirim_data(null, "Practitioner?identifier=https://fhir.kemkes.go.id/id/nik|{$nik}");

            if (isset($res['entry'][0]['resource']['id'])) {
                return $res;
            }
        }

        return ['kode' => 404, 'pesan' => 'Data Dokter Tidak Lengkap, Segera lengkapi terlebih dahulu!'];
    }

    public function get_location($location_id){
        return $this->kirim_data(null, "Location/{$location_id}");
    }

    public function createLocation(array $data){
        $payload = [
            'resourceType' => 'Location',
            'status'       => $data['status'] ?? 'active',
            'name'         => $data['name'],
            'physicalType' => [
                'coding' => [
                    [
                        'system'  => 'http://terminology.hl7.org/CodeSystem/location-physical-type',
                        'code'    => $data['physical_type_code'] ?? 'ro',
                        'display' => $data['physical_type_display'] ?? 'room',
                    ],
                ],
            ],
        ];

        if (!empty($data['identifier'])) {
            $payload['identifier'] = $data['identifier'];
        }

        if (!empty($data['description'])) {
            $payload['description'] = $data['description'];
        }

        if (!empty($data['telecom'])) {
            $payload['telecom'] = $data['telecom'];
        }

        if (!empty($data['address'])) {
            $payload['address'] = $data['address'];
        }

        if (!empty($data['managingOrganization'])) {
            $payload['managingOrganization'] = [
                'reference' => 'Organization/' . $data['managingOrganization'],
            ];
        }

        return $this->kirim_data($payload, 'Location');
    }

    public function get_organization($org_id = null){
        if ($org_id) {
            return $this->kirim_data(null, "Organization/{$org_id}");
        }

        // Search by name
        $name = urlencode(trim(func_get_arg(0)));
        return $this->kirim_data(null, "Organization?name={$name}");
    }
}
