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
                    ->getResultArray();
    }
}