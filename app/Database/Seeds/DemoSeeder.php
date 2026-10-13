<?php

namespace App\Database\Seeds;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Seeder;

/**
 * Data contoh lengkap untuk pengembangan tim.
 * Aman dijalankan berulang: data yang sudah ada dilewati, tidak jadi dobel.
 *
 * Jalankan: php spark db:seed DemoSeeder
 *
 * Akun yang dibuat (semua password: password123)
 *   Pembimbing : pembimbing@pkl.test
 *   Siswa      : siswa1@pkl.test ... siswa5@pkl.test
 */
class DemoSeeder extends Seeder
{
    public function run()
    {
        mt_srand(42); // angka acak selalu sama di semua komputer

        $now  = date('Y-m-d H:i:s');
        $pass = password_hash('password123', PASSWORD_DEFAULT);

        $mentorId = $this->userId('pembimbing@pkl.test', 'Pak Pembimbing', 'pembimbing', $pass, $now);

        // ---------- Tempat PKL ----------
        $companyIds = [];
        foreach ([
            ['PT Contoh Teknologi', 'Jl. Contoh No. 1, Kediri', '0354-000000', 'Bu Rina'],
            ['CV Digital Kreasi', 'Jl. Veteran No. 25, Kediri', '0354-111222', 'Pak Hendra'],
            ['Dinas Kominfo Kota', 'Jl. Brawijaya No. 10, Kediri', '0354-333444', 'Bu Sari'],
        ] as [$name, $address, $phone, $pic]) {
            $companyIds[] = $this->companyId($name, $address, $phone, $pic, $now);
        }

        // ---------- Siswa ----------
        $people = [
            ['Budi Santoso',  'siswa1@pkl.test', '20240001', 'RPL', 0],
            ['Siti Aisyah',   'siswa2@pkl.test', '20240002', 'RPL', 0],
            ['Rizky Pratama', 'siswa3@pkl.test', '20240003', 'TKJ', 1],
            ['Dewi Lestari',  'siswa4@pkl.test', '20240004', 'TKJ', 1],
            ['Andi Wijaya',   'siswa5@pkl.test', '20240005', 'RPL', 2],
        ];

        // Nilai [disiplin, keterampilan, sikap, laporan]; siswa 4 dan 5 sengaja belum dinilai
        $scores = [
            0 => [85, 88, 90, 80],
            1 => [92, 90, 94, 88],
            2 => [75, 80, 78, 70],
        ];

        $activities = [
            'Mempelajari alur kerja tim dan pengenalan lingkungan kantor.',
            'Membantu instalasi dan konfigurasi perangkat komputer pegawai.',
            'Membuat halaman web sederhana menggunakan HTML dan CSS.',
            'Mengerjakan perbaikan bug pada aplikasi internal perusahaan.',
            'Mendokumentasikan prosedur kerja dan membuat laporan mingguan.',
            'Membantu pemeliharaan jaringan dan pengecekan kabel LAN.',
            'Belajar membuat query database MySQL untuk laporan data.',
            'Mengikuti rapat evaluasi dan mencatat hasil pembahasan.',
        ];
        $izinReasons  = ['Keperluan keluarga', 'Mengurus dokumen sekolah', 'Acara keluarga'];
        $sakitReasons = ['Demam dan flu', 'Sakit kepala, istirahat di rumah', 'Sakit perut'];

        $start = date('Y-m-d', strtotime('-20 days'));
        $end   = date('Y-m-d', strtotime('+70 days'));

        foreach ($people as $idx => [$name, $email, $nis, $jurusan, $ci]) {
            $userId    = $this->userId($email, $name, 'siswa', $pass, $now);
            $studentId = $this->studentId($userId, [
                'company_id' => $companyIds[$ci],
                'mentor_id'  => $mentorId,
                'nis'        => $nis,
                'kelas'      => 'XII',
                'jurusan'    => $jurusan,
                'start_date' => $start,
                'end_date'   => $end,
            ], $now);

            // 14 hari terakhir (tanpa hari ini, supaya tombol "Absen Masuk" bisa dites), hanya hari kerja
            for ($i = 14; $i >= 1; $i--) {
                $ts = strtotime("-$i days");
                if ((int) date('N', $ts) >= 6) {
                    continue;
                }
                $date = date('Y-m-d', $ts);

                $r      = mt_rand(1, 100);
                $status = $r <= 84 ? 'hadir' : ($r <= 92 ? 'izin' : 'sakit');

                if ($status === 'hadir') {
                    $this->insertOnce('attendances', ['student_id' => $studentId, 'date' => $date], [
                        'check_in'  => sprintf('%02d:%02d:00', mt_rand(1, 10) <= 8 ? 7 : 8, mt_rand(0, 59)),
                        'check_out' => sprintf('16:%02d:00', mt_rand(0, 45)),
                        'status'    => 'hadir',
                    ], $now);

                    // Jurnal: lama = disetujui, agak baru = campur, terbaru = menunggu
                    $note = null;
                    if ($i > 7) {
                        $jStatus = 'approved';
                    } elseif ($i > 3) {
                        if (mt_rand(1, 10) <= 7) {
                            $jStatus = 'approved';
                        } else {
                            $jStatus = 'rejected';
                            $note    = 'Kegiatan kurang jelas, mohon diperjelas.';
                        }
                    } else {
                        $jStatus = 'pending';
                    }

                    $this->insertOnce('journals', ['student_id' => $studentId, 'date' => $date], [
                        'activity'    => $activities[mt_rand(0, count($activities) - 1)],
                        'evidence'    => null,
                        'status'      => $jStatus,
                        'mentor_note' => $note,
                    ], $now);
                } else {
                    $pool   = $status === 'izin' ? $izinReasons : $sakitReasons;
                    $reason = $pool[mt_rand(0, count($pool) - 1)];

                    $this->insertOnce('attendances', ['student_id' => $studentId, 'date' => $date], [
                        'status' => $status,
                        'note'   => $reason,
                    ], $now);

                    // Izin yang disetujui (sama seperti perilaku aplikasi)
                    $this->insertOnce('leave_requests', ['student_id' => $studentId, 'date' => $date], [
                        'type'   => $status,
                        'reason' => $reason,
                        'status' => 'approved',
                    ], $now);
                }
            }

            // Pengajuan izin tambahan supaya semua status terlihat di tampilan
            if ($idx === 0) {
                $this->insertOnce('leave_requests', [
                    'student_id' => $studentId,
                    'date'       => date('Y-m-d', strtotime('+1 day')),
                ], ['type' => 'izin', 'reason' => 'Keperluan keluarga', 'status' => 'pending'], $now);
            }
            if ($idx === 1) {
                $this->insertOnce('leave_requests', [
                    'student_id' => $studentId,
                    'date'       => date('Y-m-d', strtotime('-6 days')),
                ], ['type' => 'izin', 'reason' => 'Ingin libur lebih awal', 'status' => 'rejected'], $now);
            }

            // Penilaian (hanya sebagian siswa)
            if (isset($scores[$idx])) {
                [$d, $k, $s, $l] = $scores[$idx];
                $this->insertOnce('assessments', ['student_id' => $studentId], [
                    'disiplin'     => $d,
                    'keterampilan' => $k,
                    'sikap'        => $s,
                    'laporan'      => $l,
                    'final_score'  => round(($d + $k + $s + $l) / 4, 2),
                    'note'         => 'Perkembangan baik, pertahankan.',
                ], $now);
            }
        }

        CLI::write('DemoSeeder selesai. Login: pembimbing@pkl.test atau siswa1@pkl.test (password: password123)', 'green');
    }

    // ------------------------------------------------------------------
    // Helper
    // ------------------------------------------------------------------

    private function exists(string $table, array $where): bool
    {
        return $this->db->table($table)->where($where)->countAllResults() > 0;
    }

    /** Insert hanya jika baris dengan kunci tersebut belum ada. */
    private function insertOnce(string $table, array $key, array $data, string $now): void
    {
        if ($this->exists($table, $key)) {
            return;
        }
        $this->db->table($table)->insert(array_merge($key, $data, ['created_at' => $now, 'updated_at' => $now]));
    }

    private function userId(string $email, string $name, string $role, string $pass, string $now): int
    {
        $row = $this->db->table('users')->where('email', $email)->get()->getRowArray();
        if ($row) {
            return (int) $row['id'];
        }
        $this->db->table('users')->insert([
            'name' => $name, 'email' => $email, 'password' => $pass, 'role' => $role,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        return (int) $this->db->insertID();
    }

    private function companyId(string $name, string $address, string $phone, string $pic, string $now): int
    {
        $row = $this->db->table('companies')->where('name', $name)->get()->getRowArray();
        if ($row) {
            return (int) $row['id'];
        }
        $this->db->table('companies')->insert([
            'name' => $name, 'address' => $address, 'phone' => $phone, 'pic' => $pic,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        return (int) $this->db->insertID();
    }

    private function studentId(int $userId, array $data, string $now): int
    {
        $row = $this->db->table('students')->where('user_id', $userId)->get()->getRowArray();
        if ($row) {
            return (int) $row['id'];
        }
        $this->db->table('students')->insert(array_merge(
            ['user_id' => $userId],
            $data,
            ['created_at' => $now, 'updated_at' => $now]
        ));

        return (int) $this->db->insertID();
    }
}