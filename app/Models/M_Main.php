<?php

namespace App\Models;

use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Model;
use CodeIgniter\Validation\ValidationInterface;

class M_Main extends Model
{
    protected $table = 'user';
    protected $db1;
    protected $db2;

    public function __construct()
    {
        parent::__construct();
        $this->db1 = \Config\Database::connect('satusehat');
        $this->db2 = \Config\Database::connect('simklinik');
    }

    // ✅ CHECK USER
    public function cekuser($username)
    {
        return $this->db1
                    ->table('user')
                    ->where('username', $username)
                    ->get()
                    ->getResultArray();
    }

    // ✅ LOGIN
    public function get_token()
    {
        return $this->db1
                    ->table('token_satset')
                    ->orderBy('id','DESC')
                    ->limit('1')
                    ->get()
                    ->getRowArray();
    }

    public function get_pasien($pasien_id)
    {
        return $this->db2->table('ms_pasien')
                    ->where('id', $pasien_id)
                    ->get()
                    ->getRowArray();
    }

    public function get_dokter($dokter_id)
    {
        return $this->db2->table('ms_dokter')
                    ->where('id', $dokter_id)
                    ->get()
                    ->getRowArray();
    }

    public function save_token($token, $expires_at)
    {
        return $this->db1->table('token_satset')->insert([
            'token'   => $token,
            'expires_in' => $expires_at,
            'tgl_act' => date('Y-m-d H:i:s')
        ]);
    }

    public function save_encounter($satusehat_id, $user_act, $kunjungan_id, $pelayanan_id)
    {
        return $this->db1->table('encounter')->insert([
            'encounter_id' => $satusehat_id,
            'user_act'     => $user_act,
            'kunjungan_id' => $kunjungan_id,
            'pelayanan_id' => $pelayanan_id,
            'tgl_create'   => date('Y-m-d H:i:s'),
        ]);
    }

    public function save_log($user_act, $kunjungan_id, $response)
    {
        return $this->db1->table('log_satusehat')->insert([
            'user_act'     => $user_act,
            'kunjungan_id' => $kunjungan_id,
            'response'     => json_encode($response),
            'tgl_act'      => date('Y-m-d H:i:s'),
        ]);
    }

    public function save_condition($satusehat_id,$user_act, $kunjungan_id, $pelayanan_id, $encounter_id, $diagnosa_id)
    {
        return $this->db1->table('conditions')->insert([
            'condition_id' => $satusehat_id,
            'user_act' => $user_act,
            'encounter_id' => $encounter_id,
            'kunjungan_id' => $kunjungan_id,
            'pelayanan_id' => $pelayanan_id,
            'diagnosa_id'  => $diagnosa_id,
            'tgl_create'   => date('Y-m-d H:i:s'),
        ]);
    }

    public function save_observation($satusehat_id, $kunjungan_id, $pelayanan_id, $encounter_id, $assesment_id, $jenis_observation)
    {
        return $this->db1->table('observation')->insert([
            'observation_id' => $satusehat_id,
            'encounter_id'   => $encounter_id,
            'kunjungan_id'   => $kunjungan_id,
            'pelayanan_id'   => $pelayanan_id,
            'assesment_id'   => $assesment_id,
            'jenis_observation'       => $jenis_observation,
            'tgl_create'     => date('Y-m-d H:i:s'),
        ]);
    }

        public function save_procedure($satusehat_id, $kunjungan_id, $pelayanan_id, $encounter_id, $tindakan_id, $nama_tindakan)
    {
        return $this->db1->table('procedures')->insert([
            'procedure_id'   => $satusehat_id,
            'encounter_id'   => $encounter_id,
            'kunjungan_id'   => $kunjungan_id,
            'pelayanan_id'   => $pelayanan_id,
            'tindakan_id'    => $tindakan_id,
            'nama_tindakan'  => $nama_tindakan,
            'tgl_create'     => date('Y-m-d H:i:s'),
        ]);
    }

    public function save_medaction($satusehat_id, $kunjungan_id, $pelayanan_id, $encounter_id, $id_resep, $id_obat)
    {
        return $this->db1->table('medication')->insert([
            'medication_id'   => $satusehat_id,
            'kunjungan_id'   => $kunjungan_id,
            'pelayanan_id'   => $pelayanan_id,
            'encounter_id'   => $encounter_id,
            'id_resep'    => $id_resep,
            'id_obat'  => $id_obat,
            'tgl_create'     => date('Y-m-d H:i:s'),
        ]);
    }

    public function save_medactionreqdis($satusehat_id,$medication_id, $kunjungan_id, $pelayanan_id, $encounter_id, $id_resep, $id_obat)
    {
        return $this->db1->table('medication')->insert([
            'medicationreqdis_id'   => $satusehat_id,
            'medication_id'   => $medication_id,
            'kunjungan_id'   => $kunjungan_id,
            'pelayanan_id'   => $pelayanan_id,
            'encounter_id'   => $encounter_id,
            'id_resep'    => $id_resep,
            'id_obat'  => $id_obat,
            'tgl_create'     => date('Y-m-d H:i:s'),
        ]);
    }

    public function save_download($kunjungan_id, $pelayanan_id, $user_act, $nama_dokter, $nik_dokter, $nama_px, $nik_px, $no_rm, $nama_unit, $ihs_unitid, $tgl_1, $tgl_2, $tgl_3, $diagnosa, $nadi, $nafas, $sistole, $diastole, $suhu, $tindakan)
    {
        return $this->db1->table('get_kunjungan')->insert([
            'kunjungan_id'   => $kunjungan_id,
            'pelayanan_id'   => $pelayanan_id,
            'user_act'   => $user_act,
            'nama_dokter'   => $nama_dokter,
            'nik_dokter'    => $nik_dokter,
            'nama_px'   => $nama_px,
            'nik_px'   => $nik_px,
            'no_rm'   => $no_rm,
            'nama_unit'   => $nama_unit,
            'ihs_unitid'    => $ihs_unitid,            
            'tgl_1'   => $tgl_1,
            'tgl_2'   => $tgl_2,
            'tgl_3'   => $tgl_3,
            'diagnosa'   => $diagnosa,
            'nadi'    => $nadi,            
            'nafas'   => $nafas,
            'sistole'   => $sistole,
            'diastole'   => $diastole,
            'suhu'   => $suhu,
            'tindakan'    => $tindakan,
            'status_kirim'   => 0,
            'retry_count'    => 0,
            'tgl_act'     => date('Y-m-d H:i:s'),
        ]);
    }

    // -----------------------------------------------------------------------
    // SIMRS queries (simklinik DB)
    // -----------------------------------------------------------------------

    public function get_kunjungan($tgl)
    {
        return $this->db2->query(
            "SELECT bk.id AS kunjungan_id, bk.pasien_id, bp.id AS pelayanan_id, bk.tgl_act,
                (CASE WHEN DATE(bk.tgl_act) < DATE(bk.tgl)
                    THEN DATE_ADD(bk.tgl_act, INTERVAL 1 DAY)
                    ELSE DATE_ADD(bk.tgl_act, INTERVAL 10 MINUTE) END) AS tgl_1,
                (CASE WHEN DATE(bk.tgl_act) < DATE(bk.tgl)
                    THEN DATE_ADD(bk.tgl_act, INTERVAL 25 HOUR)
                    ELSE DATE_ADD(bk.tgl_act, INTERVAL 30 MINUTE) END) AS tgl_2,
                (CASE WHEN DATE(bk.tgl_act) < DATE(bk.tgl)
                    THEN DATE_ADD(bk.tgl_act, INTERVAL 26 HOUR)
                    ELSE DATE_ADD(bk.tgl_act, INTERVAL 60 MINUTE) END) AS tgl_3,
                peg.nama AS nama_dokter, peg.nik AS nik_dokter,
                bmp.nama AS nama_px, bmp.no_ktp as nik_px, bmp.no_rm,
                mu.nama AS nama_unit, mu.ihs_id as ihs_unitid
            FROM b_kunjungan bk
            LEFT JOIN ms_pasien bmp ON bk.pasien_id = bmp.id
            LEFT JOIN b_pelayanan bp ON bk.id = bp.kunjungan_id
            LEFT JOIN users peg ON bp.dokter_id = peg.id_user
            LEFT JOIN b_ms_unit mu ON bp.unit_id = mu.id
            WHERE DATE(bk.tgl_act) = ? GROUP BY bk.id",
            [$tgl]
        )->getResult();
    }

    public function get_diagnosa($pelayanan_id)
    {
        return $this->db2->query(
            "SELECT bd.id, bd.ms_diagnosa_id, bmd.diagnosis, bmd.kode_icd10
            FROM b_diagnosa bd
            JOIN b_ms_diagnosa bmd ON bd.ms_diagnosa_id = bmd.id
            WHERE bd.pelayanan_id = ?
            ORDER BY bd.primer DESC",
            [$pelayanan_id]
        )->getResult();
    }

    public function get_observasi($pelayanan_id)
    {
        // echo $pelayanan_id;die();
        return $this->db2->query(
            "SELECT s.id, s.td AS tekanan,SUBSTRING_INDEX(s.td, '/', 1) AS sistole,    SUBSTRING_INDEX(s.td, '/', -1) AS diastole, s.nadi, s.suhu, s.frekuensi_nfs AS nafas,s.user_act, s.tgl_soap, u.nik AS nik_dokter_obs
            FROM soap s
            LEFT JOIN users u ON s.user_act = u.id_user
            WHERE s.pelayanan_id = ?",
            [$pelayanan_id]
        )->getResult();
    }

    public function get_tindakan($pelayanan_id)
    {
        return $this->db2->query(
            "SELECT t.id AS tindakan_id, t.user_act, t.tgl_act,
                mt.nama AS nama_tind, mt.kode_icd9cm
            FROM b_tindakan t
            JOIN b_ms_tindakan_kelas mtk ON t.ms_tindakan_kelas_id = mtk.id
            JOIN b_ms_tindakan mt ON mtk.ms_tindakan_id = mt.id
            WHERE t.pelayanan_id = ?
            AND mt.nama NOT IN('Karcis Poli','Karcis BPJS','Jasa Dokter Spesialis BPJS',
                'Jasa Dokter Spesialis','JASA DOKTER BPJS RUJUK 2 POLI')",
            [$pelayanan_id]
        )->getResult();
    }

    public function get_resep($kunjungan_id)
    {
        // echo $pelayanan_id;die();
        return $this->db2->query(
            "SELECT r.*, o.KODE_KFA93, o.NAMA_OBAT_KFA93 FROM klinik_bersama.resep r JOIN klinik_bersama.ms_obat o ON r.obat_id = o.OBAT_ID WHERE r.kunjungan_id ? AND o.KODE_KFA93 IS NOT NULL",
            [$kunjungan_id]
        )->getResult();
    }

    public function is_already_sent($pelayanan_id)
    {
        return $this->db1->table('encounter')
            ->where('pelayanan_id', $pelayanan_id)
            ->countAllResults() > 0;
    }

    // -----------------------------------------------------------------------
    // Bundle transaction methods
    // -----------------------------------------------------------------------

    public function get_pending_kunjungan(string $statusColumn = 'status_kirim')
    {
        return $this->db1->table('get_kunjungan')
            ->where($statusColumn, 0)
            ->get()
            ->getResult();
    }

    public function update_status_success($pelayanan_id, string $statusColumn = 'status_kirim')
    {
        return $this->db1->table('get_kunjungan')
            ->where('pelayanan_id', $pelayanan_id)
            ->update([$statusColumn => 1, 'tgl_act' => date('Y-m-d H:i:s')]);
    }

    public function update_status_failed($pelayanan_id, string $statusColumn = 'status_kirim', ?string $retryColumn = 'retry_count')
    {
        $builder = $this->db1->table('get_kunjungan')
            ->where('pelayanan_id', $pelayanan_id)
            ->set($statusColumn, 2)
            ->set('tgl_act', date('Y-m-d H:i:s'));

        if (!empty($retryColumn)) {
            $builder->set($retryColumn, "{$retryColumn} + 1", false);
        }

        return $builder->update();
    }

    public function update_status_success_medication($pelayanan_id)
    {
        return $this->update_status_success($pelayanan_id, 'status_kirimmed');
    }

    public function update_status_failed_medication($pelayanan_id)
    {
        return $this->update_status_failed($pelayanan_id, 'status_kirimmed', null);
    }

}
