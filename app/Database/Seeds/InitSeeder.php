<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $this->db->table('users')->insert([
            'name' => 'Pak Pembimbing', 'email' => 'pembimbing@pkl.test',
            'password' => password_hash('password123', PASSWORD_DEFAULT), 'role' => 'pembimbing',
            'created_at' => $now,
        ]);
        $mentorId = $this->db->insertID();

        $this->db->table('companies')->insert([
            'name' => 'PT Contoh Teknologi', 'address' => 'Jl. Contoh No. 1, Kediri',
            'phone' => '0354-000000', 'pic' => 'Bu Rina', 'created_at' => $now,
        ]);
        $companyId = $this->db->insertID();

        $this->db->table('users')->insert([
            'name' => 'Siswa Contoh', 'email' => 'siswa@pkl.test',
            'password' => password_hash('password123', PASSWORD_DEFAULT), 'role' => 'siswa',
            'created_at' => $now,
        ]);
        $studentUserId = $this->db->insertID();

        $this->db->table('students')->insert([
            'user_id' => $studentUserId, 'company_id' => $companyId, 'mentor_id' => $mentorId,
            'nis' => '12345', 'kelas' => 'XII', 'jurusan' => 'RPL',
            'start_date' => date('Y-m-01'), 'end_date' => date('Y-m-d', strtotime('+3 months')),
            'created_at' => $now,
        ]);
    }
}
