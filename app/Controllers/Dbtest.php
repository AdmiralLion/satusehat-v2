<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Dbtest extends Controller
{
    public function index()
    {
        $results = [];

        // Test satusehat connection
        try {
            $db1 = \Config\Database::connect('satusehat');
            $connected1 = $db1->connect();
            $query1 = $db1->query('SELECT DATABASE() AS db, NOW() AS now');
            $row1 = $query1->getRow();
            $results['satusehat'] = [
                'status'  => 'OK',
                'message' => 'Connected successfully',
                'database' => $row1->db,
                'time'    => $row1->now,
            ];
        } catch (\Throwable $e) {
            $results['satusehat'] = [
                'status'  => 'FAIL',
                'message' => $e->getMessage(),
            ];
        }

        // Test simklinik connection
        try {
            $db2 = \Config\Database::connect('simklinik');
            $connected2 = $db2->connect();
            $query2 = $db2->query('SELECT DATABASE() AS db, NOW() AS now');
            $row2 = $query2->getRow();
            $results['simklinik'] = [
                'status'  => 'OK',
                'message' => 'Connected successfully',
                'database' => $row2->db,
                'time'    => $row2->now,
            ];
        } catch (\Throwable $e) {
            $results['simklinik'] = [
                'status'  => 'FAIL',
                'message' => $e->getMessage(),
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($results, JSON_PRETTY_PRINT);
        exit;
    }
}
