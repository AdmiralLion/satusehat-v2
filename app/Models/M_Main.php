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
}