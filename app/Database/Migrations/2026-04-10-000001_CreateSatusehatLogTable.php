<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSatusehatLogTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'local_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'resource_type' => ['type' => 'VARCHAR', 'constraint' => 50],
            'satu_sehat_id' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'payload'       => ['type' => 'TEXT', 'null' => true],
            'response'      => ['type' => 'TEXT', 'null' => true],
            'status'        => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'success'],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['local_id', 'resource_type']);
        $this->forge->createTable('satusehat_log');
    }

    public function down()
    {
        $this->forge->dropTable('satusehat_log');
    }
}
