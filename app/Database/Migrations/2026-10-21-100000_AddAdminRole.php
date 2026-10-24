<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Menambah role 'admin' pada kolom users.role (sebelumnya hanya siswa & pembimbing).
 * Data lama tidak berubah.
 */
class AddAdminRole extends Migration
{
    public function up()
    {
        $table = $this->db->prefixTable('users');
        $this->db->query("ALTER TABLE `{$table}` MODIFY `role` ENUM('siswa','pembimbing','admin') NOT NULL DEFAULT 'siswa'");
    }

    public function down()
    {
        $table = $this->db->prefixTable('users');

        // ENUM tidak boleh menyisakan nilai 'admin' saat dikecilkan lagi.
        // Akun admin diturunkan jadi pembimbing supaya tidak ada akun yang hilang.
        $this->db->query("UPDATE `{$table}` SET `role` = 'pembimbing' WHERE `role` = 'admin'");
        $this->db->query("ALTER TABLE `{$table}` MODIFY `role` ENUM('siswa','pembimbing') NOT NULL DEFAULT 'siswa'");
    }
}
