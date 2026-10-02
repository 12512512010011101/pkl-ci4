<?php

namespace App\Controllers;

use App\Models\StudentModel;

/**
 * Controller dasar untuk semua controller aplikasi PKL.
 * (Meng-extend BaseController bawaan CI4)
 */
abstract class PklController extends BaseController
{
    protected $helpers = ['url', 'form'];

    protected function isSiswa(): bool
    {
        return session('role') === 'siswa';
    }

    /** Data siswa yang sedang login (khusus role siswa) */
    protected function myStudent(): ?array
    {
        return model(StudentModel::class)->detail()->where('students.user_id', session('user_id'))->first();
    }

    /** ID semua siswa bimbingan pembimbing yang sedang login */
    protected function myStudentIds(): array
    {
        $rows = model(StudentModel::class)->where('mentor_id', session('user_id'))->findAll();

        return $rows ? array_column($rows, 'id') : [0];
    }

    protected function fail(string $msg)
    {
        return redirect()->back()->withInput()->with('error', $msg);
    }
}
