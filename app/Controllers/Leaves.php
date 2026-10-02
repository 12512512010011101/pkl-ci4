<?php

namespace App\Controllers;

use App\Models\AttendanceModel;
use App\Models\LeaveModel;

class Leaves extends PklController
{
    public function index()
    {
        $lm = model(LeaveModel::class);

        if ($this->isSiswa()) {
            $s    = $this->myStudent();
            $rows = $s ? $lm->where('student_id', $s['id'])->orderBy('date', 'DESC')->findAll() : [];

            return view('leaves/index', ['rows' => $rows]);
        }

        $rows = $lm->select('leave_requests.*, users.name')
            ->join('students', 'students.id = leave_requests.student_id')
            ->join('users', 'users.id = students.user_id')
            ->whereIn('leave_requests.student_id', $this->myStudentIds())
            ->orderBy('leave_requests.date', 'DESC')->findAll(300);

        return view('leaves/index', ['rows' => $rows]);
    }

    public function create()
    {
        $s = $this->myStudent();
        if (! $s) {
            return $this->fail('Data siswa belum dilengkapi pembimbing.');
        }
        if (! $this->validate([
            'date'   => 'required|valid_date',
            'type'   => 'required|in_list[izin,sakit]',
            'reason' => 'required|min_length[5]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        model(LeaveModel::class)->insert([
            'student_id' => $s['id'], 'date' => $this->request->getPost('date'),
            'type' => $this->request->getPost('type'), 'reason' => $this->request->getPost('reason'),
            'status' => 'pending',
        ]);

        return redirect()->to('/leaves')->with('success', 'Pengajuan terkirim.');
    }

    public function review(int $id)
    {
        $lm     = model(LeaveModel::class);
        $l      = $lm->find($id);
        $action = $this->request->getPost('action');

        if (! $l || ! in_array($l['student_id'], $this->myStudentIds()) || ! in_array($action, ['approve', 'reject'], true)) {
            return redirect()->back()->with('error', 'Data tidak valid.');
        }

        $db = db_connect();
        $db->transStart();
        $lm->update($id, ['status' => $action === 'approve' ? 'approved' : 'rejected']);

        // Jika disetujui, otomatis tercatat di absensi
        if ($action === 'approve') {
            $am = model(AttendanceModel::class);
            $ex = $am->where('student_id', $l['student_id'])->where('date', $l['date'])->first();
            $data = ['status' => $l['type'], 'note' => $l['reason']];
            if ($ex) {
                $am->update($ex['id'], $data);
            } else {
                $am->insert($data + ['student_id' => $l['student_id'], 'date' => $l['date']]);
            }
        }
        $db->transComplete();

        return redirect()->back()->with('success', 'Pengajuan diperbarui.');
    }
}
