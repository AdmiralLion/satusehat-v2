<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use GuzzleHttp\Client;
use Config\Satusehat;
use App\Models\M_Main;
use Ramsey\Uuid\Uuid;

class SatuSehatApi extends Controller
{
    protected Client $httpClient;
    protected Satusehat $config;
    protected ?string $token = null;
    protected $m_main;
    use ResponseTrait;

    public function __construct()
    {
        $this->m_main = new M_Main();
        $this->config     = config('Satusehat');
        $this->httpClient = new Client([
            'timeout' => 30,
            'verify'  => false,
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
    protected function fetchToken(): ?string
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

            if (isset($body['access_token'])) {
                $this->token = $body['access_token'];
                return $this->token;
            }

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
        return $this->fetchToken() !== null;
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

    public function kirim_data($payload, $namafunc){
        $data_token = $this->m_main->get_token();
        $now = date('d-m-Y H:i:s');
        $timediff = strtotime($data_token['tgl_act']) - strtotime($now);
        $token = $data_token['token'];
        $new_token = $this->getToken();
        dd($new_token);
        if($timediff > $now){
            $new_token = $this->getToken();
            dd($new_token);
        }
    }

    public function get_pasien_nik($nik){

    }
}
