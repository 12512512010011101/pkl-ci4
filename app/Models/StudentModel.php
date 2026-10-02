<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentModel extends Model
{
    protected $table         = 'students';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['user_id', 'company_id', 'mentor_id', 'nis', 'kelas', 'jurusan', 'start_date', 'end_date'];

    /**
     * Query siswa lengkap: nama, perusahaan, rekap absensi, jurnal, dan nilai.
     * Lanjutkan dengan ->where(...) / ->findAll() / ->first()
     */
    public function detail()
    {
        return $this->select("students.*, users.name, users.email, companies.name AS company_name,
            (SELECT COUNT(*) FROM attendances a WHERE a.student_id = students.id AND a.status = 'hadir') AS hadir,
            (SELECT COUNT(*) FROM attendances a WHERE a.student_id = students.id AND a.status = 'izin') AS izin,
            (SELECT COUNT(*) FROM attendances a WHERE a.student_id = students.id AND a.status = 'sakit') AS sakit,
            (SELECT COUNT(*) FROM journals j WHERE j.student_id = students.id AND j.status = 'approved') AS jurnal_ok,
            (SELECT COUNT(*) FROM journals j WHERE j.student_id = students.id) AS jurnal_all,
            (SELECT s.final_score FROM assessments s WHERE s.student_id = students.id LIMIT 1) AS nilai", false)
            ->join('users', 'users.id = students.user_id')
            ->join('companies', 'companies.id = students.company_id', 'left');
    }

    /** Progres PKL (0-100) berdasarkan tanggal mulai - selesai */
    public static function progress(array $s): int
    {
        $start = strtotime($s['start_date']);
        $end   = strtotime($s['end_date']);
        if (! $start || ! $end || $end <= $start) {
            return 0;
        }

        return (int) max(0, min(100, round((time() - $start) / ($end - $start) * 100)));
    }
}
