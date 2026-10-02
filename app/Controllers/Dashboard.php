<?php

namespace App\Controllers;

use App\Models\JournalModel;
use App\Models\LeaveModel;
use App\Models\StudentModel;

class Dashboard extends PklController
{
    public function index()
    {
        if ($this->isSiswa()) {
            return view('dashboard/index', ['s' => $this->myStudent(), 'students' => [], 'stat' => []]);
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
}
