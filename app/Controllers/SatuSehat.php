<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\M_Main;
use App\Controllers\SatuSehatApi;
use Ramsey\Uuid\Uuid;

class SatuSehat extends Controller
{
    use ResponseTrait;
    protected $api;
    protected $m_main;

    public function __construct()
    {
        $this->api = new SatuSehatApi();
        $this->m_main = new M_Main();
        // $this->m_main = new M_Main();
    }

    // -----------------------------------------------------------------------
    // FHIR Encounter endpoints
    // -----------------------------------------------------------------------

    /**
     * GET /encounter
     * List/search encounters with optional filters.
     */
    public function index()
    {  

        $getdata = $this->m_main->cekuser('oni');
        // $params = [
        //     'organization' => $this->request->getGet('organization'),
        //     'patient'      => $this->request->getGet('patient'),
        //     'practitioner' => $this->request->getGet('practitioner'),
        //     'status'       => $this->request->getGet('status'),
        //     'date'         => $this->request->getGet('date'),
        // ];

        // $params = array_filter($params, fn($v) => $v !== null && $v !== '');

        // $result = $this->api->get('Encounter', $params);

        // return $this->respond($result);
        // dd($getdata);
        return $getdata;
    }

    /**
     * GET /encounter/{id}
     * Get a single encounter by Satu Sehat ID.
     */

    public function send_encounter(){
        $input = $this->request->getJSON(true);
        $payload       = $input['payload'] ?? [];
        $kunjungan_id  = $input['kunjungan_id'] ?? null;
        $pelayanan_id  = $input['pelayanan_id'] ?? null;
        $user_act      = $input['user_act'] ?? null;

        $result = $this->api->kirim_data($payload, 'Encounter',$user_act,$kunjungan_id);
        if(isset($result['id'])){
            $this->m_main->save_encounter(
                $result['id'],
                $user_act,
                $kunjungan_id,
                $pelayanan_id
            );
            return $this->respond([
                'kode'  => 200,
                'pesan' => 'Data Berhasil Terkirim'
            ], 200);
        } else {
            return $this->respond([
                'kode'  => 400,
                'pesan' => $result['body']['issue'][0]['details']['text']
            ], 404);
        }
    }


    public function show(string $id): ResponseInterface
    {
        $result = $this->api->get("Encounter/{$id}");

        return $this->respond($result);
    }

    /**
     * POST /encounter
     * Create a new encounter in Satu Sehat.
     *
     * Expected JSON body:
     * {
     *   "status": "finished",
     *   "class_code": "AMB",
     *   "class_display": "ambulatory",
     *   "subject_id": "<patient-ihs-id>",
     *   "subject_display": "<patient-name>",
     *   "participants": [
     *     {
     *       "practitioner_id": "<practitioner-ihs-id>",
     *       "practitioner_display": "<doctor-name>"
     *     }
     *   ],
     *   "period_start": "2024-01-01T08:00:00+07:00",
     *   "period_end": "2024-01-01T09:00:00+07:00",
     *   "diagnoses": [
     *     {
     *       "condition_id": "<condition-ihs-id>",
     *       "condition_display": "<diagnosis-name>",
     *       "rank": 1,
     *       "role_code": "CC",
     *       "role_display": "Chief complaint"
     *     }
     *   ],
     *   "location_id": "<location-ihs-id>",
     *   "location_display": "<location-name>",
     *   "reason_code": "<icd10-code>",
     *   "reason_display": "<reason-text>"
     * }
     */
    public function create(): ResponseInterface
    {
        $input = $this->request->getJSON(true);

        if (!$input) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Invalid JSON body',
            ], 400);
        }

        $payload = $this->buildEncounterPayload($input);
        $result  = $this->api->post('Encounter', $payload);

        return $this->respond($result);
    }

    /**
     * PUT /encounter/{id}
     * Update an existing encounter in Satu Sehat.
     *
     * Same JSON body as create, plus the encounter ID in URL.
     */
    public function update(string $id): ResponseInterface
    {
        $input = $this->request->getJSON(true);

        if (!$input) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Invalid JSON body',
            ], 400);
        }

        $payload         = $this->buildEncounterPayload($input);
        $payload['id']   = $id;
        $result          = $this->api->put("Encounter/{$id}", $payload);

        return $this->respond($result);
    }

    // -----------------------------------------------------------------------
    // Bridge: push local encounter data to Satu Sehat
    // -----------------------------------------------------------------------

    /**
     * POST /encounter/bridge
     * Bridge local encounter data from simklinik DB to Satu Sehat API.
     *
     * Expected JSON body:
     * {
     *   "local_encounter_id": 123
     * }
     *
     * Or query parameter: /encounter/bridge?local_id=123
     */
    public function bridge(): ResponseInterface
    {
        $localId = $this->request->getGet('local_id')
                   ?? $this->request->getJSON(true)['local_encounter_id']
                   ?? null;

        if (!$localId) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'local_encounter_id is required',
            ], 400);
        }

        $localData = $this->getLocalEncounter($localId);

        if (!$localData) {
            return $this->respond([
                'status'  => 'error',
                'message' => "Local encounter #{$localId} not found",
            ], 404);
        }

        $payload = $this->mapLocalToFhir($localData);
        $result  = $this->api->post('Encounter', $payload);

        // Optionally store the Satu Sehat response back to local DB
        if (isset($result['id'])) {
            $this->saveSatuSehatResponse($localId, $result['id'], $result);
        }

        return $this->respond([
            'status'       => 'success',
            'local_id'     => $localId,
            'satu_sehat'   => $result,
        ]);
    }

    // -----------------------------------------------------------------------
    // Payload builders
    // -----------------------------------------------------------------------

    /**
     * Build FHIR R4 Encounter payload from input array.
     */
    protected function buildEncounterPayload(array $input): array
    {
        $payload = [
            'resourceType' => 'Encounter',
            'status'       => $input['status'] ?? 'finished',
            'class'        => [
                'system'  => $input['class_system'] ?? 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
                'code'    => $input['class_code'] ?? 'AMB',
                'display' => $input['class_display'] ?? 'ambulatory',
            ],
            'subject' => [
                'reference' => 'Patient/' . ($input['subject_id'] ?? ''),
                'display'   => $input['subject_display'] ?? '',
            ],
            'period' => [
                'start' => $input['period_start'] ?? date('c'),
            ],
        ];

        if (!empty($input['period_end'])) {
            $payload['period']['end'] = $input['period_end'];
        }

        // Participants
        if (!empty($input['participants'])) {
            foreach ($input['participants'] as $p) {
                $payload['participant'][] = [
                    'individual' => [
                        'reference' => 'Practitioner/' . ($p['practitioner_id'] ?? ''),
                        'display'   => $p['practitioner_display'] ?? '',
                    ],
                ];
            }
        }

        // Diagnoses
        if (!empty($input['diagnoses'])) {
            foreach ($input['diagnoses'] as $d) {
                $diagnosis = [
                    'condition' => [
                        'reference' => 'Condition/' . ($d['condition_id'] ?? ''),
                        'display'   => $d['condition_display'] ?? '',
                    ],
                ];
                if (isset($d['rank'])) {
                    $diagnosis['rank'] = (int) $d['rank'];
                }
                if (isset($d['role_code'])) {
                    $diagnosis['use'] = [
                        'system'  => 'http://terminology.hl7.org/CodeSystem/diagnosis-role',
                        'code'    => $d['role_code'],
                        'display' => $d['role_display'] ?? '',
                    ];
                }
                $payload['diagnosis'][] = $diagnosis;
            }
        }

        // Location
        if (!empty($input['location_id'])) {
            $payload['location'][] = [
                'location' => [
                    'reference' => 'Location/' . $input['location_id'],
                    'display'   => $input['location_display'] ?? '',
                ],
            ];
        }

        // Reason
        if (!empty($input['reason_code'])) {
            $payload['reasonCode'][] = [
                'coding' => [
                    [
                        'system'  => 'http://hl7.org/fhir/sid/icd-10',
                        'code'    => $input['reason_code'],
                        'display' => $input['reason_display'] ?? '',
                    ],
                ],
            ];
        }

        // Status history
        if (!empty($input['status_history'])) {
            foreach ($input['status_history'] as $sh) {
                $payload['statusHistory'][] = [
                    'status' => $sh['status'] ?? 'arrived',
                    'period' => [
                        'start' => $sh['period_start'] ?? date('c'),
                        'end'   => $sh['period_end'] ?? date('c'),
                    ],
                ];
            }
        }

        return $payload;
    }

    /**
     * Fetch local encounter data from simklinik database.
     * Adjust the table name and column mappings to match your simklinik schema.
     */
    protected function getLocalEncounter(int $localId): ?object
    {
        $db = \Config\Database::connect('simklinik');

        return $db->table('pendaftaran p')
            ->select([
                'p.id',
                'p.no_registrasi',
                'p.tanggal_daftar',
                'p.tanggal_pulang',
                'p.status_pendaftaran',
                'p.jenis_kunjungan',
                'pasien.no_rm',
                'pasien.nama as pasien_nama',
                'pasien.nik',
                'pasien.id_satusehat as pasien_ihs_id',
                'dokter.nama as dokter_nama',
                'dokter.id_satusehat as dokter_ihs_id',
                'poli.nama as poli_nama',
                'poli.id_satusehat as poli_ihs_id',
                'diagnosa.kode_icd',
                'diagnosa.nama_diagnosa',
            ])
            ->join('pasien', 'pasien.id = p.pasien_id', 'left')
            ->join('dokter', 'dokter.id = p.dokter_id', 'left')
            ->join('poli', 'poli.id = p.poli_id', 'left')
            ->join('diagnosa', 'diagnosa.pendaftaran_id = p.id', 'left')
            ->where('p.id', $localId)
            ->get()
            ->getRow();
    }

    /**
     * Map local encounter data to FHIR Encounter payload.
     * Adjust mappings according to your simklinik schema.
     */
    protected function mapLocalToFhir(object $local): array
    {
        $statusMap = [
            'batal'    => 'cancelled',
            'daftar'   => 'arrived',
            'proses'   => 'in-progress',
            'selesai'  => 'finished',
        ];

        $classMap = [
            'Rawat Jalan' => ['AMB', 'ambulatory'],
            'Rawat Inap'  => ['IMP', 'inpatient encounter'],
            'IGD'         => ['EMER', 'emergency'],
        ];

        $jenisKunjungan = $local->jenis_kunjungan ?? 'Rawat Jalan';
        $classInfo      = $classMap[$jenisKunjungan] ?? ['AMB', 'ambulatory'];
        $status         = $statusMap[$local->status_pendaftaran] ?? 'finished';

        $input = [
            'status'        => $status,
            'class_code'    => $classInfo[0],
            'class_display' => $classInfo[1],
            'subject_id'    => $local->pasien_ihs_id,
            'subject_display' => $local->pasien_nama,
            'period_start'  => $local->tanggal_daftar,
            'period_end'    => $local->tanggal_pulang,
        ];

        if (!empty($local->dokter_ihs_id)) {
            $input['participants'][] = [
                'practitioner_id'      => $local->dokter_ihs_id,
                'practitioner_display' => $local->dokter_nama,
            ];
        }

        if (!empty($local->poli_ihs_id)) {
            $input['location_id']     = $local->poli_ihs_id;
            $input['location_display'] = $local->poli_nama;
        }

        if (!empty($local->kode_icd)) {
            $input['diagnoses'][] = [
                'condition_id'      => '',
                'condition_display' => $local->nama_diagnosa,
                'rank'              => 1,
                'role_code'         => 'CC',
                'role_display'      => 'Chief complaint',
            ];

            $input['reason_code']    = $local->kode_icd;
            $input['reason_display'] = $local->nama_diagnosa;
        }

        return $this->buildEncounterPayload($input);
    }

    /**
     * Save the Satu Sehat encounter ID back to the local database.
     */
    protected function saveSatuSehatResponse(int $localId, string $ssId, array $response): void
    {
        $db = \Config\Database::connect('satusehat');

        $db->table('encounter_log')->insert([
            'local_encounter_id'   => $localId,
            'satu_sehat_id'        => $ssId,
            'resource_type'        => 'Encounter',
            'payload'              => json_encode($response),
            'created_at'           => date('Y-m-d H:i:s'),
        ]);
    }
}
