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

        // $getdata = $this->m_main->cekuser('oni');
        // return $getdata;
    }

    public function download_data(){
        $data = $this->m_main->get_kunjungan(date('Y-m-d'));
        foreach ($data as $i):
            $diag = $this->m_main->get_diagnosa($i->pelayanan_id);
            $obs = $this->m_main->get_observasi($i->pelayanan_id);
            $proc = $this->m_main->get_tindakan($i->pelayanan_id);
            if($i->nik_px != '' AND $i->nik_px != '-' AND $i->nik_px != null){
                $this->m_main->save_download(
                    $i->kunjungan_id ?? null,
                    $i->pelayanan_id ?? null,
                    $i->user_act  ?? null,        
                    $i->nama_dokter ?? null,
                    $i->nik_dokter ?? null,
                    $i->nama_px ?? null,               
                    $i->nik_px ?? null,
                    $i->no_rm ?? null,
                    $i->nama_unit ?? null,               
                    $i->ihs_unitid ?? null,
                    $i->tgl_1 ?? null,
                    $i->tgl_2 ?? null,              
                    $i->tgl_3 ?? null,
                    $diag[0]->id ?? null,
                    $obs[0]->nadi ?? null,               
                    $obs[0]->nafas ?? null,
                    $obs[0]->sistole ?? null,
                    $obs[0]->diastole ?? null,                
                    $obs[0]->suhu ?? null,
                    $proc[0]->tindakan ?? null
                );
            }
        endforeach;
    }

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

    public function send_condition(){
        $input = $this->request->getJSON(true);
        $payload       = $input['payload'] ?? [];
        $kunjungan_id  = $input['kunjungan_id'] ?? null;
        $pelayanan_id  = $input['pelayanan_id'] ?? null;
        $user_act      = $input['user_act'] ?? null;
        $encounter_id  = $input['encounter_id'] ?? null;
        $diagnosa_id   = $input['diagnosa_id'] ?? null;

        $result = $this->api->kirim_data($payload, 'Condition', $user_act, $kunjungan_id);
        if(isset($result['id'])){
            $this->m_main->save_condition(
                $result['id'],
                $user_act,
                $kunjungan_id,
                $pelayanan_id,
                $encounter_id,
                $diagnosa_id
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

    public function send_observation(){
        $input = $this->request->getJSON(true);
        $payload       = $input['payload'] ?? [];
        $kunjungan_id  = $input['kunjungan_id'] ?? null;
        $pelayanan_id  = $input['pelayanan_id'] ?? null;
        $user_act      = $input['user_act'] ?? null;
        $encounter_id  = $input['encounter_id'] ?? null;
        $assesment_id  = $input['assesment_id'] ?? null;
        $jenis_observation      = $input['jenis_observation'] ?? null;

        $result = $this->api->kirim_data($payload, 'Observation', $user_act, $kunjungan_id);
        if(isset($result['id'])){
            $this->m_main->save_observation(
                $result['id'],
                $kunjungan_id,
                $pelayanan_id,
                $encounter_id,
                $assesment_id,
                $jenis_observation
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

    public function send_procedure(){
        $input = $this->request->getJSON(true);
        $payload       = $input['payload'] ?? [];
        $kunjungan_id  = $input['kunjungan_id'] ?? null;
        $pelayanan_id  = $input['pelayanan_id'] ?? null;
        $user_act      = $input['user_act'] ?? null;
        $encounter_id  = $input['encounter_id'] ?? null;
        $tindakan_id   = $input['tindakan_id'] ?? null;
        $nama_tindakan = $input['nama_tindakan'] ?? null;

        $result = $this->api->kirim_data($payload, 'Procedure', $user_act, $kunjungan_id);
        if(isset($result['id'])){
            $this->m_main->save_procedure(
                $result['id'],
                $kunjungan_id,
                $pelayanan_id,
                $encounter_id,
                $tindakan_id,
                $nama_tindakan
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

    public function send_medication(?object $row = null, ?string $org_id = null){
        $input = $this->request->getJSON(true) ?? null;
        $payload       = $input['payload'] ?? [];
        $kunjungan_id  = $input['kunjungan_id'] ?? null;
        $pelayanan_id  = $input['pelayanan_id'] ?? null;
        $user_act      = $input['user_act'] ?? null;
        $encounter_id   = $input['encounter_id'] ?? null;
        $id_resep   = $input['id_resep'] ?? null;
        $id_obat = $input['id_obat'] ?? null;

        if($input == null){
            // $pending = $this->m_main->get_pending_kunjungan();
            $ihsPatient = $this->resolveIhsByNik($row->nik_px, 'Patient');
            $ihsDoctor  = $this->resolveIhsByNik($row->nik_dokter, 'Practitioner');

            // $ihsDoctor = '10009880728'; //ihs_staging
            // $ihsPatient = 'P02478375538'; //ihs_staging
            // 2. Get detailed data from SIMRS
            $obat_detail    = $this->m_main->get_resep($row->kunjungan_id);
            foreach($obat_detail as $i): //besok finish ini
                
            endforeach;
            $payload = [
                    'resourceType' => 'Medication',
                    'meta' => [
                        'profile' => [
                            'https://fhir.kemkes.go.id/r4/StructureDefinition/Medication'
                        ]
                    ],
                    'identifier' => [
                        [
                            'system' => "http://sys-ids.kemkes.go.id/medication/'$org_id'",
                            'use' => 'official',
                            'value' => "{$obat_detail['obat_id']}"
                        ]
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://sys-ids.kemkes.go.id/kfa',
                                'code' => "{$obat_detail['KODE_KFA']}",
                                'display' => "{$obat_detail['NAMA_KFA']}"
                            ]
                        ]
                    ],
                    'status' => 'active',
                    'extension' => [
                        [
                            'url' => 'https://fhir.kemkes.go.id/r4/StructureDefinition/MedicationType',
                            'valueCodeableConcept' => [
                                'coding' => [
                                    [
                                        'system' => 'http://terminology.kemkes.go.id/CodeSystem/medication-type',
                                        'code' => 'NC',
                                        'display' => 'Non-compound'
                                    ]
                                ]
                            ]
                        ]
                    ]
            ];
        }

        $result = $this->api->kirim_data($payload, 'Medication', $user_act, $kunjungan_id);
        if(isset($result['id'])){
            $this->m_main->save_medication(
                $result['id'],
                $kunjungan_id,
                $pelayanan_id,
                $encounter_id,
                $id_resep,
                $id_obat
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

    // -----------------------------------------------------------------------
    // Bundle Transaction (Encounter + Condition + Observation + Procedure)
    // -----------------------------------------------------------------------

    /**
     * POST/GET /send_bundle
     * Sends all pending kunjungan as FHIR Bundle transactions.
     * Called automatically by cron after download_data().
     */
    public function send_bundle()
    {
        $org_id  = config('Satusehat')->organizationId;
        $pending = $this->m_main->get_pending_kunjungan();

        if (empty($pending)) {
            return $this->respond([
                'kode'  => 400,
                'pesan' => 'Tidak ada data yang perlu dikirim',
            ]);
        }

        $berhasil = 0;
        $gagal    = 0;
        $errors   = [];

        foreach ($pending as $row) {
            try {
                $this->processBundle($row, $org_id);
                $berhasil++;
            } catch (\RuntimeException $e) {
                $this->m_main->update_status_failed($row->pelayanan_id);
                $this->m_main->save_log(null, $row->kunjungan_id, [
                    'kode' => 400,
                    'pesan' => $e->getMessage(),
                ]);
                $errors[] = [
                    'pelayanan_id' => $row->pelayanan_id,
                    'error'        => $e->getMessage(),
                ];
                $gagal++;
            }
        }

        return $this->respond([
            'kode'  => 200,
            'pesan' => "Proses bundle selesai: {$berhasil} berhasil, {$gagal} gagal",
            'total' => count($pending),
            'errors' => $errors,
        ]);
    }

    public function send_medication_bundle()
    {
        $org_id  = config('Satusehat')->organizationId;
        $pending = $this->m_main->get_pending_kunjungan('status_kirimmed');

        if (empty($pending)) {
            return $this->respond([
                'kode'  => 400,
                'pesan' => 'Tidak ada data yang perlu dikirim',
            ]);
        }

        $berhasil = 0;
        $gagal    = 0;
        $errors   = [];

        foreach ($pending as $row) {
            try {
                $result = $this->send_medication($row, $org_id);
                $isSuccess = method_exists($result, 'getStatusCode')
                    ? $result->getStatusCode() < 400
                    : true;

                if (!$isSuccess) {
                    throw new \RuntimeException('Pengiriman medication bundle gagal.');
                }

                $this->m_main->update_status_success_medication($row->pelayanan_id);
                $berhasil++;
            } catch (\RuntimeException $e) {
                $this->m_main->update_status_failed_medication($row->pelayanan_id);
                $this->m_main->save_log(null, $row->kunjungan_id, [
                    'kode' => 400,
                    'pesan' => $e->getMessage(),
                ]);
                $errors[] = [
                    'pelayanan_id' => $row->pelayanan_id,
                    'error'        => $e->getMessage(),
                ];
                $gagal++;
            }
        }

        return $this->respond([
            'kode'  => 200,
            'pesan' => "Proses bundle selesai: {$berhasil} berhasil, {$gagal} gagal",
            'total' => count($pending),
            'errors' => $errors,
        ]);
    }

    /**
     * Resolve SatuSehat IHS ID by NIK.
     */
    protected function resolveIhsByNik(string $nik, string $resourceType): string
    {
        $result = $this->api->kirim_data(
            null,
            "{$resourceType}?identifier=https://fhir.kemkes.go.id/id/nik|{$nik}"
        );

        if (isset($result['entry'][0]['resource']['id'])) {
            return $result['entry'][0]['resource']['id'];
        }

        throw new \RuntimeException("IHS ID tidak ditemukan untuk {$resourceType} NIK: {$nik}");
    }

    /**
     * Process a single kunjungan record into a FHIR Bundle and send it.
     */
    protected function processBundle(object $row, string $org_id): void
    {
        // 1. Resolve patient & doctor IHS
        $ihsPatient = $this->resolveIhsByNik($row->nik_px, 'Patient');
        $ihsDoctor  = $this->resolveIhsByNik($row->nik_dokter, 'Practitioner');

        // $ihsDoctor = '10009880728'; //ihs_staging
        // $ihsPatient = 'P02478375538'; //ihs_staging
        // 2. Get detailed data from SIMRS
        $diagnoses    = $this->m_main->get_diagnosa($row->pelayanan_id);
        $observations = $this->m_main->get_observasi($row->pelayanan_id);
        $procedures   = $this->m_main->get_tindakan($row->pelayanan_id);

        // 3. Resolve observation doctor IHS (fallback to encounter doctor)
        $ihsDoctorObs = $ihsDoctor;
        $obsData      = null;
        if (!empty($observations)) {
            $obsData = $observations[0];
            if (!empty($obsData->nik_dokter_obs)) {
                try {
                    $ihsDoctorObs = $this->resolveIhsByNik($obsData->nik_dokter_obs, 'Practitioner');
                } catch (\RuntimeException $e) {
                    // fallback to encounter doctor
                }
            }
        }

        // 4. Generate UUIDs
        $uuids = [
            'encounter' => Uuid::uuid4()->toString(),
            'nadi'      => Uuid::uuid4()->toString(),
            'nafas'     => Uuid::uuid4()->toString(),
            'sistole'   => Uuid::uuid4()->toString(),
            'diastole'  => Uuid::uuid4()->toString(),
            'suhu'      => Uuid::uuid4()->toString(),
        ];

        $conditionUuids = [];
        foreach ($diagnoses as $i => $diag) {
            $conditionUuids[$i] = Uuid::uuid4()->toString();
        }

        $procedureUuids = [];
        foreach ($procedures as $i => $proc) {
            $procedureUuids[$i] = Uuid::uuid4()->toString();
        }

        // 5. Build bundle
        $bundle = $this->buildBundle($row, $uuids, $conditionUuids, $procedureUuids, [
            'org_id'        => $org_id,
            'ihs_patient'   => $ihsPatient,
            'ihs_doctor'    => $ihsDoctor,
            'ihs_doctor_obs' => $ihsDoctorObs,
            'diagnoses'     => $diagnoses,
            'obsData'       => $obsData,
            'procedures'    => $procedures,
            'nadi'          => (int) $row->nadi,
            'nafas'         => (int) $row->nafas,
            'sistole'       => (int) $row->sistole,
            'diastole'      => (int) $row->diastole,
            'suhu'          => (int) $row->suhu,
        ]);

        // 6. Send bundle
        $result = $this->api->kirim_data($bundle, '', null, $row->kunjungan_id);

        // 7. Parse response and save IDs
        $this->handleBundleResponse($result, $row);

        // 8. Update status
        $this->m_main->update_status_success($row->pelayanan_id);
    }

    /**
     * Build the full FHIR Bundle array.
     */
    protected function buildBundle(object $row, array $uuids, array $conditionUuids, array $procedureUuids, array $params): array
    {
        $tglKunj      = convertTimeSatset($row->tgl_1);
        $tglPelayanan = convertTimeSatset($row->tgl_2);
        $tglPulang    = convertTimeSatset($row->tgl_3);
        $hariKunj     = hariIndo(date('l', strtotime($row->tgl_1)));

        $entries = [];
        // --- Encounter Entry ---
        $encounterDiagnosis = [];
        foreach ($params['diagnoses'] as $i => $diag) {
            $rank = min($i + 1, 2);
            $encounterDiagnosis[] = [
                'condition' => [
                    'reference' => "urn:uuid:{$conditionUuids[$i]}",
                    'display'   => $diag->diagnosis,
                ],
                'use' => [
                    'coding' => [[
                        'system'  => 'http://terminology.hl7.org/CodeSystem/diagnosis-role',
                        'code'    => 'DD',
                        'display' => 'Discharge diagnosis',
                    ]],
                ],
                'rank' => $rank,
            ];
        }

       $encounterResource = [
            'resourceType' => 'Encounter',
            'status'       => 'finished',
            'class'        => [
                'system'  => 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
                'code'    => 'AMB',
                'display' => 'ambulatory',
            ],
            'subject' => [
                'reference' => "Patient/{$params['ihs_patient']}",
                'display'   => $row->nama_px,
            ],
            'participant' => [[
                'type' => [[
                    'coding' => [[
                        'system'  => 'http://terminology.hl7.org/CodeSystem/v3-ParticipationType',
                        'code'    => 'ATND',
                        'display' => 'attender',
                    ]],
                ]],
                'individual' => [
                    'reference' => "Practitioner/{$params['ihs_doctor']}",
                    'display'   => $row->nama_dokter,
                ],
            ]],
            'period' => [
                'start' => $tglKunj,
                'end'   => $tglKunj,
            ],
            'location' => [[
                'location' => [
                    'reference' => "Location/{$row->ihs_unitid}",
                    'display'   => $row->nama_unit,
                ],
            ]],
            'statusHistory' => [
                [
                    'status' => 'arrived',
                    'period' => ['start' => $tglKunj, 'end' => $tglKunj],
                ],
                [
                    'status' => 'in-progress',
                    'period' => ['start' => $tglPelayanan, 'end' => $tglPulang],
                ],
                [
                    'status' => 'finished',
                    'period' => ['start' => $tglPulang, 'end' => $tglPulang],
                ],
            ],
            'serviceProvider' => [
                'reference' => "Organization/{$params['org_id']}",
            ],
            'identifier' => [[
                'system' => "http://sys-ids.kemkes.go.id/encounter/{$params['org_id']}",
                'value'  => (string) $row->pelayanan_id,
            ]],
        ]; 

        if (!empty($encounterDiagnosis)) {
            $encounterResource['diagnosis'] = $encounterDiagnosis;
        }

        $entries[] = [
            'fullUrl'  => "urn:uuid:{$uuids['encounter']}",
            'resource' => $encounterResource,
            'request'  => [
                'method' => 'POST',
                'url'    => 'Encounter',
            ],
        ];

        // --- Condition Entries ---
        foreach ($params['diagnoses'] as $i => $diag) {
            $entries[] = $this->buildConditionEntry($conditionUuids[$i], $uuids['encounter'], $diag, $params, $hariKunj, $tglKunj,$row->nama_px);
        }

        // --- Observation Entries ---
        if ($params['obsData']) {
            $obsEffective = convertTimeSatset($params['obsData']->tgl_soap);
            $hariObs      = hariIndo(date('l', strtotime($params['obsData']->tgl_soap)));

            $obsParams = [
                'ihs_patient'    => $params['ihs_patient'],
                'ihs_doctor_obs' => $params['ihs_doctor_obs'],
                'uuid_encounter' => $uuids['encounter'],
                'nama_px'        => $row->nama_px,
                'hari_obs'       => $hariObs,
                'tgl_kunj'       => $tglKunj,
                'effective_date' => $obsEffective,
                'pelayanan_id'   => $row->pelayanan_id,
                'org_id'         => $params['org_id'],
            ];

            if ($params['nadi'] > 0) {
                $entries[] = $this->buildObservationEntry($uuids['nadi'], 'nadi', $obsParams, $params['nadi']);
            }
            if ($params['nafas'] > 0) {
                $entries[] = $this->buildObservationEntry($uuids['nafas'], 'nafas', $obsParams, $params['nafas']);
            }
            if ($params['sistole'] > 0) {
                $entries[] = $this->buildObservationEntry($uuids['sistole'], 'sistole', $obsParams, $params['sistole']);
            }
            if ($params['diastole'] > 0) {
                $entries[] = $this->buildObservationEntry($uuids['diastole'], 'diastole', $obsParams, $params['diastole']);
            }
            if ($params['suhu'] > 0) {
                $entries[] = $this->buildObservationEntry($uuids['suhu'], 'suhu', $obsParams, $params['suhu']);
            }
        }

        // --- Procedure Entries ---
        foreach ($params['procedures'] as $i => $proc) {
            if (empty($proc->kode_icd9cm)) {
                continue;
            }
            $entries[] = $this->buildProcedureEntry($procedureUuids[$i], $uuids['encounter'], $proc, $params, $row);
        }

        return [
            'resourceType' => 'Bundle',
            'type'         => 'transaction',
            'entry'        => $entries,
        ];
    }

    /**
     * Build a single Condition entry for the Bundle.
     */
    protected function buildConditionEntry(string $uuid, string $uuidEncounter, object $diag, array $params, string $hariKunj, string $tglKunj, $nama_px): array
    {
        return [
            'fullUrl' => "urn:uuid:{$uuid}",
            'resource' => [
                'resourceType' => 'Condition',
                'clinicalStatus' => [
                    'coding' => [[
                        'system'  => 'http://terminology.hl7.org/CodeSystem/condition-clinical',
                        'code'    => 'active',
                        'display' => 'Active',
                    ]],
                ],
                'category' => [[
                    'coding' => [[
                        'system'  => 'http://terminology.hl7.org/CodeSystem/condition-category',
                        'code'    => 'encounter-diagnosis',
                        'display' => 'Encounter Diagnosis',
                    ]],
                ]],
                'code' => [
                    'coding' => [[
                        'system'  => 'http://hl7.org/fhir/sid/icd-10',
                        'code'    => $diag->kode_icd10,
                        'display' => $diag->diagnosis,
                    ]],
                ],
                'subject' => [
                    'reference' => "Patient/{$params['ihs_patient']}",
                    'display'   => $nama_px ?? '',
                ],
                'encounter' => [
                    'reference' => "urn:uuid:{$uuidEncounter}",
                    'display'   => "Kunjungan di hari {$hariKunj}, {$tglKunj}",
                ],
                'identifier' => [[
                    'system' => "http://sys-ids.kemkes.go.id/condition/{$params['org_id']}",
                    'value'  => (string) $diag->id,
                ]],
            ],
            'request' => [
                'method' => 'POST',
                'url'    => 'Condition',
            ],
        ];
    }

    /**
     * Build a single Observation entry for the Bundle.
     */
    protected function buildObservationEntry(string $uuid, string $type, array $params, int $value): array
    {
        $obsConfig = [
            'nadi' => [
                'code' => '8867-4', 'display' => 'Heart rate',
                'unit' => 'beats/minute', 'system' => 'http://unitsofmeasure.org', 'unit_code' => '/min',
                'label' => 'Denyut Jantung',
            ],
            'nafas' => [
                'code' => '9279-1', 'display' => 'Respiratory rate',
                'unit' => 'breaths/minute', 'system' => 'http://unitsofmeasure.org', 'unit_code' => '/min',
                'label' => 'Pernafasan',
            ],
            'sistole' => [
                'code' => '8480-6', 'display' => 'Systolic blood pressure',
                'unit' => 'mm[Hg]', 'system' => 'http://unitsofmeasure.org', 'unit_code' => 'mm[Hg]',
                'label' => 'Sistole',
                'bodySite' => ['coding' => [[
                    'system' => 'http://snomed.info/sct', 'code' => '368209003', 'display' => 'Right arm',
                ]]],
            ],
            'diastole' => [
                'code' => '8462-4', 'display' => 'Diastolic blood pressure',
                'unit' => 'mm[Hg]', 'system' => 'http://unitsofmeasure.org', 'unit_code' => 'mm[Hg]',
                'label' => 'Diastole',
                'bodySite' => ['coding' => [[
                    'system' => 'http://snomed.info/sct', 'code' => '368209003', 'display' => 'Right arm',
                ]]],
            ],
            'suhu' => [
                'code' => '8310-5', 'display' => 'Body temperature',
                'unit' => 'C', 'system' => 'http://unitsofmeasure.org', 'unit_code' => 'Cel',
                'label' => 'Suhu Tubuh',
            ],
        ];

        $cfg = $obsConfig[$type];

        $resource = [
            'resourceType' => 'Observation',
            'status'       => 'final',
            'category' => [[
                'coding' => [[
                    'system'  => 'http://terminology.hl7.org/CodeSystem/observation-category',
                    'code'    => 'vital-signs',
                    'display' => 'Vital Signs',
                ]],
            ]],
            'code' => [
                'coding' => [[
                    'system'  => 'http://loinc.org',
                    'code'    => $cfg['code'],
                    'display' => $cfg['display'],
                ]],
            ],
            'subject' => [
                'reference' => "Patient/{$params['ihs_patient']}",
            ],
            'performer' => [[
                'reference' => "Practitioner/{$params['ihs_doctor_obs']}",
            ]],
            'encounter' => [
                'reference' => "urn:uuid:{$params['uuid_encounter']}",
                'display'   => "Pemeriksaan Fisik {$cfg['label']} {$params['nama_px']} di hari {$params['hari_obs']}, {$params['tgl_kunj']}",
            ],
            'effectiveDateTime' => $params['effective_date'],
            'issued'            => $params['effective_date'],
            'valueQuantity' => [
                'value'  => $value,
                'unit'   => $cfg['unit'],
                'system' => $cfg['system'],
                'code'   => $cfg['unit_code'],
            ],
            'identifier' => [[
                'system' => "http://sys-ids.kemkes.go.id/observation/{$params['org_id']}",
                'value'  => (string) $params['pelayanan_id'],
            ]],
        ];

        if (isset($cfg['bodySite'])) {
            $resource['bodySite'] = $cfg['bodySite'];
        }

        if ($type === 'suhu') {
            $interp = getTemperatureInterpretation($value);
            $resource['interpretation'] = [[
                'coding' => [[
                    'system'  => 'http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation',
                    'code'    => $interp['code'],
                    'display' => $interp['display'],
                ]],
                'text' => $interp['text'],
            ]];
        }

        return [
            'fullUrl'  => "urn:uuid:{$uuid}",
            'resource' => $resource,
            'request'  => [
                'method' => 'POST',
                'url'    => 'Observation',
            ],
        ];
    }

    /**
     * Build a single Procedure entry for the Bundle.
     */
    protected function buildProcedureEntry(string $uuid, string $uuidEncounter, object $proc, array $params, object $row): array
    {
        $tglTindakan = convertTimeSatset($proc->tgl_act);
        $hari        = hariIndo(date('l', strtotime($proc->tgl_act)));

        return [
            'fullUrl' => "urn:uuid:{$uuid}",
            'resource' => [
                'resourceType' => 'Procedure',
                'status'       => 'completed',
                'category'     => [
                    'coding' => [[
                        'system'  => 'http://snomed.info/sct',
                        'code'    => '103693007',
                        'display' => 'Diagnostic procedure',
                    ]],
                    'text' => 'Diagnostic procedure',
                ],
                'code' => [
                    'coding' => [[
                        'system'  => 'http://hl7.org/fhir/sid/icd-9-cm',
                        'code'    => $proc->kode_icd9cm,
                        'display' => $proc->nama_tind,
                    ]],
                ],
                'subject' => [
                    'reference' => "Patient/{$params['ihs_patient']}",
                    'display'   => $row->nama_px,
                ],
                'encounter' => [
                    'reference' => "urn:uuid:{$uuidEncounter}",
                    'display'   => "Tindakan {$proc->nama_tind} {$row->nama_px} pada {$hari}, " . date('d-m-Y', strtotime($proc->tgl_act)),
                ],
                'performedPeriod' => [
                    'start' => $tglTindakan,
                    'end'   => $tglTindakan,
                ],
                'performer' => [[
                    'actor' => [
                        'reference' => "Practitioner/{$params['ihs_doctor']}",
                        'display'   => $row->nama_dokter,
                    ],
                ]],
                'identifier' => [[
                    'system' => "http://sys-ids.kemkes.go.id/procedure/{$params['org_id']}",
                    'value'  => (string) $row->pelayanan_id,
                ]],
            ],
            'request' => [
                'method' => 'POST',
                'url'    => 'Procedure',
            ],
        ];
    }

    /**
     * Parse Bundle response from SatuSehat and save resource IDs.
     */
    protected function handleBundleResponse(array $response, object $row): void
    {
        if (!isset($response['entry'])) {
            throw new \RuntimeException('Bundle response tidak valid: ' . json_encode($response));
        }

        $encounter_id = null;
        $obsCounter   = 0;
        $obsNames     = ['Denyut Jantung', 'Pernafasan', 'Sistole', 'Diastole', 'Suhu Tubuh'];

        foreach ($response['entry'] as $entry) {
            $resp         = $entry['response'] ?? [];
            $resourceType = $resp['resourceType'] ?? '';
            $resourceId   = $resp['resourceID'] ?? '';

            if ($resourceType === 'Encounter') {
                $encounter_id = $resourceId;
                $this->m_main->save_encounter($resourceId, $row->user_act, $row->kunjungan_id, $row->pelayanan_id);
            } elseif ($resourceType === 'Condition') {
                $this->m_main->save_condition($resourceId, $row->user_act, $row->kunjungan_id, $row->pelayanan_id, $encounter_id, null);
            } elseif ($resourceType === 'Observation') {
                $jenis = $obsNames[$obsCounter] ?? 'Observation';
                $this->m_main->save_observation($resourceId, $row->kunjungan_id, $row->pelayanan_id, $encounter_id, null, $jenis);
                $obsCounter++;
            } elseif ($resourceType === 'Procedure') {
                $this->m_main->save_procedure($resourceId, $row->kunjungan_id, $row->pelayanan_id, $encounter_id, null, null);
            }
        }
    }
}
