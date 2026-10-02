<?php

namespace App\Controllers;

use App\Models\AssessmentModel;
use App\Models\StudentModel;

class Assessments extends PklController
{
    private function mine(int $studentId): ?array
    {
        return model(StudentModel::class)->detail()
            ->where('students.id', $studentId)->where('students.mentor_id', session('user_id'))->first();
    }

    public function index()
    {
        $rows = model(StudentModel::class)->detail()->where('students.mentor_id', session('user_id'))->orderBy('users.name')->findAll();

        return view('assessments/index', ['rows' => $rows]);
    }

    public function edit(int $studentId)
    {
        $s = $this->mine($studentId);
        if (! $s) {
            return redirect()->to('/assessments')->with('error', 'Siswa tidak ditemukan.');
        }

        return view('assessments/form', ['s' => $s, 'a' => model(AssessmentModel::class)->where('student_id', $studentId)->first()]);
    }

    public function save(int $studentId)
    {
        if (! $this->mine($studentId)) {
            return redirect()->to('/assessments')->with('error', 'Siswa tidak ditemukan.');
        }

        $num = 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]';
        if (! $this->validate(['disiplin' => $num, 'keterampilan' => $num, 'sikap' => $num, 'laporan' => $num])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $p    = $this->request->getPost(['disiplin', 'keterampilan', 'sikap', 'laporan', 'note']);
        $data = [
            'disiplin' => (int) $p['disiplin'], 'keterampilan' => (int) $p['keterampilan'],
            'sikap' => (int) $p['sikap'], 'laporan' => (int) $p['laporan'], 'note' => $p['note'],
        ];
        $data['final_score'] = round(($data['disiplin'] + $data['keterampilan'] + $data['sikap'] + $data['laporan']) / 4, 2);

        $am = model(AssessmentModel::class);
        $ex = $am->where('student_id', $studentId)->first();
        $ex ? $am->update($ex['id'], $data) : $am->insert($data + ['student_id' => $studentId]);

        return redirect()->to('/assessments')->with('success', 'Penilaian tersimpan.');
    }
}
