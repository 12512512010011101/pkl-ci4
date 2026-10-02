<?php

namespace App\Controllers;

use App\Models\AttendanceModel;

class Attendance extends PklController
{
    public function index()
    {
        $at = model(AttendanceModel::class);

        if ($this->isSiswa()) {
            $s     = $this->myStudent();
            $rows  = $s ? $at->where('student_id', $s['id'])->orderBy('date', 'DESC')->findAll() : [];
            $today = $s ? $at->where('student_id', $s['id'])->where('date', date('Y-m-d'))->first() : null;

            return view('attendance/index', ['rows' => $rows, 'today' => $today, 's' => $s]);
        }

        $rows = $at->select('attendances.*, users.name')
            ->join('students', 'students.id = attendances.student_id')
            ->join('users', 'users.id = students.user_id')
            ->whereIn('attendances.student_id', $this->myStudentIds())
            ->orderBy('attendances.date', 'DESC')->findAll(300);

        return view('attendance/index', ['rows' => $rows, 'today' => null, 's' => null]);
    }

    public function checkin()
    {
        $s = $this->myStudent();
        if (! $s) {
            return redirect()->back()->with('error', 'Data siswa belum dilengkapi pembimbing.');
        }
        if ($s['start_date'] > date('Y-m-d') || $s['end_date'] < date('Y-m-d')) {
            return redirect()->back()->with('error', 'Di luar periode PKL.');
        }

        $at = model(AttendanceModel::class);
        if ($at->where('student_id', $s['id'])->where('date', date('Y-m-d'))->first()) {
            return redirect()->back()->with('error', 'Kamu sudah absen hari ini.');
        }

        $at->insert(['student_id' => $s['id'], 'date' => date('Y-m-d'), 'check_in' => date('H:i:s'), 'status' => 'hadir']);

        return redirect()->to('/attendance')->with('success', 'Absen masuk tercatat.');
    }

    public function checkout()
    {
        $s = $this->myStudent();
        $at = model(AttendanceModel::class);
        $rec = $s ? $at->where('student_id', $s['id'])->where('date', date('Y-m-d'))->first() : null;

        if (! $rec || $rec['status'] !== 'hadir') {
            return redirect()->back()->with('error', 'Belum ada absen masuk hari ini.');
        }
        $at->update($rec['id'], ['check_out' => date('H:i:s')]);

        return redirect()->to('/attendance')->with('success', 'Absen pulang tercatat.');
    }
}
