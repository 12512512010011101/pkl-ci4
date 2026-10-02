<?php

namespace App\Controllers;

use App\Models\JournalModel;

class Journals extends PklController
{
    public function index()
    {
        $jm = model(JournalModel::class);

        if ($this->isSiswa()) {
            $s    = $this->myStudent();
            $rows = $s ? $jm->where('student_id', $s['id'])->orderBy('date', 'DESC')->findAll() : [];

            return view('journals/index', ['rows' => $rows]);
        }

        $jm->select('journals.*, users.name')
            ->join('students', 'students.id = journals.student_id')
            ->join('users', 'users.id = students.user_id')
            ->whereIn('journals.student_id', $this->myStudentIds());

        $status = $this->request->getGet('status');
        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $jm->where('journals.status', $status);
        }

        return view('journals/index', ['rows' => $jm->orderBy('journals.date', 'DESC')->findAll(300)]);
    }

    public function add()
    {
        return view('journals/form');
    }

    public function create()
    {
        $s = $this->myStudent();
        if (! $s) {
            return $this->fail('Data siswa belum dilengkapi pembimbing.');
        }

        $rules = ['date' => 'required|valid_date', 'activity' => 'required|min_length[10]'];
        $file  = $this->request->getFile('evidence');
        $hasFile = $file && $file->getError() !== UPLOAD_ERR_NO_FILE;
        if ($hasFile) {
            $rules['evidence'] = 'uploaded[evidence]|max_size[evidence,2048]|ext_in[evidence,jpg,jpeg,png,pdf]';
        }
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $date = $this->request->getPost('date');
        if ($date > date('Y-m-d')) {
            return $this->fail('Tanggal jurnal tidak boleh di masa depan.');
        }

        $name = null;
        if ($hasFile && $file->isValid() && ! $file->hasMoved()) {
            $name = $file->getRandomName();
            $file->move(FCPATH . 'uploads/journals', $name);
        }

        model(JournalModel::class)->insert([
            'student_id' => $s['id'], 'date' => $date,
            'activity' => $this->request->getPost('activity'), 'evidence' => $name, 'status' => 'pending',
        ]);

        return redirect()->to('/journals')->with('success', 'Jurnal tersimpan, menunggu persetujuan pembimbing.');
    }

    public function delete(int $id)
    {
        $s = $this->myStudent();
        $j = model(JournalModel::class)->find($id);

        if (! $s || ! $j || $j['student_id'] != $s['id'] || $j['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Jurnal tidak bisa dihapus.');
        }
        if ($j['evidence'] && is_file(FCPATH . 'uploads/journals/' . $j['evidence'])) {
            unlink(FCPATH . 'uploads/journals/' . $j['evidence']);
        }
        model(JournalModel::class)->delete($id);

        return redirect()->to('/journals')->with('success', 'Jurnal dihapus.');
    }

    public function review(int $id)
    {
        $jm     = model(JournalModel::class);
        $j      = $jm->find($id);
        $action = $this->request->getPost('action');

        if (! $j || ! in_array($j['student_id'], $this->myStudentIds()) || ! in_array($action, ['approve', 'reject'], true)) {
            return redirect()->back()->with('error', 'Data tidak valid.');
        }
        $jm->update($id, [
            'status' => $action === 'approve' ? 'approved' : 'rejected',
            'mentor_note' => $this->request->getPost('mentor_note'),
        ]);

        return redirect()->back()->with('success', 'Jurnal diperbarui.');
    }
}
