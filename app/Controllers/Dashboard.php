<?php

namespace App\Controllers;

use App\Models\AttendanceModel;
use App\Models\CompanyModel;
use App\Models\JournalModel;
use App\Models\LeaveModel;
use App\Models\StudentModel;
use App\Models\UserModel;

class Dashboard extends PklController
{
    public function index()
    {
        if ($this->isSiswa()) {
            return view('dashboard/index', ['s' => $this->myStudent(), 'students' => [], 'stat' => []]);
        }

        if (session('role') === 'admin') {
            return view('dashboard/admin', $this->adminData());
        }

        $ids      = $this->myStudentIds();
        $students = model(StudentModel::class)->detail()->where('students.mentor_id', session('user_id'))->findAll();
        $stat     = [
            'siswa'  => count($students),
            'jurnal' => model(JournalModel::class)->whereIn('student_id', $ids)->where('status', 'pending')->countAllResults(),
            'izin'   => model(LeaveModel::class)->whereIn('student_id', $ids)->where('status', 'pending')->countAllResults(),
        ];

        return view('dashboard/index', ['s' => null, 'students' => $students, 'stat' => $stat]);
    }

    /**
     * Data dashboard admin (ringkasan seluruh sekolah).
     * Variabel untuk view dashboard/admin: $stat, $mentors, $unassigned.
     */
    private function adminData(): array
    {
        $stat = [
            'siswa'            => model(StudentModel::class)->countAllResults(),
            'pembimbing'       => model(UserModel::class)->where('role', 'pembimbing')->countAllResults(),
            'perusahaan'       => model(CompanyModel::class)->countAllResults(),
            'hadir_hari_ini'   => model(AttendanceModel::class)->where('date', date('Y-m-d'))->where('status', 'hadir')->countAllResults(),
            'jurnal_pending'   => model(JournalModel::class)->where('status', 'pending')->countAllResults(),
            'izin_pending'     => model(LeaveModel::class)->where('status', 'pending')->countAllResults(),
            'tanpa_pembimbing' => model(StudentModel::class)->where('mentor_id', null)->countAllResults(),
            'tanpa_tempat'     => model(StudentModel::class)->where('company_id', null)->countAllResults(),
        ];

        // Pembimbing beserta jumlah siswa bimbingannya
        $mentors = db_connect()->query(
            "SELECT u.id, u.name, u.email,
                    (SELECT COUNT(*) FROM students s WHERE s.mentor_id = u.id) AS jumlah_siswa
             FROM users u WHERE u.role = 'pembimbing' ORDER BY u.name"
        )->getResultArray();

        // Siswa yang belum punya pembimbing atau tempat PKL (maksimal 10)
        $unassigned = model(StudentModel::class)->detail()
            ->groupStart()->where('students.mentor_id', null)->orWhere('students.company_id', null)->groupEnd()
            ->orderBy('users.name')->findAll(10);

        return ['stat' => $stat, 'mentors' => $mentors, 'unassigned' => $unassigned];
    }
}
