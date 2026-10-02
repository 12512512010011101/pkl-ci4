<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePklTables extends Migration
{
    private function id(): array
    {
        return ['id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true]];
    }

    private function fk(bool $null = false): array
    {
        return ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => $null];
    }

    private function ts(): array
    {
        return [
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ];
    }

    public function up()
    {
        // USERS
        $this->forge->addField($this->id() + [
            'name'     => ['type' => 'VARCHAR', 'constraint' => 100],
            'email'    => ['type' => 'VARCHAR', 'constraint' => 150],
            'password' => ['type' => 'VARCHAR', 'constraint' => 255],
            'role'     => ['type' => 'ENUM', 'constraint' => ['siswa', 'pembimbing'], 'default' => 'siswa'],
        ] + $this->ts());
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('users');

        // COMPANIES
        $this->forge->addField($this->id() + [
            'name'    => ['type' => 'VARCHAR', 'constraint' => 150],
            'address' => ['type' => 'TEXT', 'null' => true],
            'phone'   => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'pic'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
        ] + $this->ts());
        $this->forge->addKey('id', true);
        $this->forge->createTable('companies');

        // STUDENTS
        $this->forge->addField($this->id() + [
            'user_id'    => $this->fk(),
            'company_id' => $this->fk(true),
            'mentor_id'  => $this->fk(true),
            'nis'        => ['type' => 'VARCHAR', 'constraint' => 30],
            'kelas'      => ['type' => 'VARCHAR', 'constraint' => 30],
            'jurusan'    => ['type' => 'VARCHAR', 'constraint' => 80],
            'start_date' => ['type' => 'DATE'],
            'end_date'   => ['type' => 'DATE'],
        ] + $this->ts());
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('mentor_id', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('students');

        // ATTENDANCES
        $this->forge->addField($this->id() + [
            'student_id' => $this->fk(),
            'date'       => ['type' => 'DATE'],
            'check_in'   => ['type' => 'TIME', 'null' => true],
            'check_out'  => ['type' => 'TIME', 'null' => true],
            'status'     => ['type' => 'ENUM', 'constraint' => ['hadir', 'izin', 'sakit'], 'default' => 'hadir'],
            'note'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ] + $this->ts());
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['student_id', 'date']);
        $this->forge->addForeignKey('student_id', 'students', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('attendances');

        // JOURNALS
        $this->forge->addField($this->id() + [
            'student_id'  => $this->fk(),
            'date'        => ['type' => 'DATE'],
            'activity'    => ['type' => 'TEXT'],
            'evidence'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'      => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'rejected'], 'default' => 'pending'],
            'mentor_note' => ['type' => 'TEXT', 'null' => true],
        ] + $this->ts());
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('student_id', 'students', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('journals');

        // LEAVE REQUESTS
        $this->forge->addField($this->id() + [
            'student_id' => $this->fk(),
            'date'       => ['type' => 'DATE'],
            'type'       => ['type' => 'ENUM', 'constraint' => ['izin', 'sakit'], 'default' => 'izin'],
            'reason'     => ['type' => 'TEXT'],
            'status'     => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'rejected'], 'default' => 'pending'],
        ] + $this->ts());
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('student_id', 'students', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('leave_requests');

        // ASSESSMENTS
        $this->forge->addField($this->id() + [
            'student_id'   => $this->fk(),
            'disiplin'     => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 0],
            'keterampilan' => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 0],
            'sikap'        => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 0],
            'laporan'      => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 0],
            'final_score'  => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'note'         => ['type' => 'TEXT', 'null' => true],
        ] + $this->ts());
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('student_id');
        $this->forge->addForeignKey('student_id', 'students', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('assessments');
    }

    public function down()
    {
        foreach (['assessments', 'leave_requests', 'journals', 'attendances', 'students', 'companies', 'users'] as $t) {
            $this->forge->dropTable($t, true);
        }
    }
}
