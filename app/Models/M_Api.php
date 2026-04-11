<?php

namespace App\Models;

use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Model;
use CodeIgniter\Validation\ValidationInterface;

class M_Api extends Model
{

    public function __construct()
    {
        $db1 = \Config\Database::connect('satusehat');
        $db2 = \Config\Database::connect('simklinik');
    }

    // ✅ REGISTER
    public function getdata($nama, $username, $password)
    {
        return $this->insert([
            'nama'     => $nama,
            'username' => $username,
            'pswd'     => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }

    // ✅ CHECK USER
    public function cekuser($username)
    {
        return $this->db1->from('user')
                    ->where('username', $username)
                    ->findAll();
    }

    // ✅ LOGIN
    public function ceklogin($username, $password)
    {
        $user = $this->where('username', $username)->first();

        if (!$user) {
            return null;
        }

        if (password_verify($password, $user['pswd'])) {
            return $user;
        }

        return null;
    }
}