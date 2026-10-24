<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Membuat akun admin contoh. Aman dijalankan berulang (tidak membuat dobel).
 *   php spark db:seed AdminSeeder
 *
 * GANTI password bawaan ini sebelum aplikasi dipakai di server sungguhan.
 */
class AdminSeeder extends Seeder
{
    public function run()
    {
        $email = 'admin@pkl.test';

        if ($this->db->table('users')->where('email', $email)->countAllResults() > 0) {
            echo "Akun {$email} sudah ada, dilewati.\n";

            return;
        }

        $this->db->table('users')->insert([
            'name'       => 'Admin PKL',
            'email'      => $email,
            'password'   => password_hash('password123', PASSWORD_DEFAULT),
            'role'       => 'admin',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        echo "Akun {$email} dibuat.\n";
    }
}
