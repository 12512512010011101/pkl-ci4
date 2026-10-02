<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\StudentModel;
use App\Models\UserModel;

class Students extends PklController
{
    private function rules(?int $userId = null): array
    {
        $r = [
            'name'       => 'required|min_length[3]',
            'email'      => 'required|valid_email|' . ($userId ? "is_unique[users.email,id,{$userId}]" : 'is_unique[users.email]'),
            'password'   => $userId ? 'permit_empty|min_length[6]' : 'required|min_length[6]',
            'nis'        => 'required|max_length[30]',
            'kelas'      => 'required|max_length[30]',
            'jurusan'    => 'required|max_length[80]',
            'company_id' => 'permit_empty|is_natural_no_zero',
            'start_date' => 'required|valid_date',
            'end_date'   => 'required|valid_date',
        ];

        return $r;
    }

    private function mine(int $id): ?array
    {
        return model(StudentModel::class)->detail()
            ->where('students.id', $id)->where('students.mentor_id', session('user_id'))->first();
    }

    public function index()
    {
        $rows = model(StudentModel::class)->detail()->where('students.mentor_id', session('user_id'))->orderBy('users.name')->findAll();

        return view('students/index', ['rows' => $rows]);
    }

    public function add()
    {
        return view('students/form', ['s' => null, 'companies' => model(CompanyModel::class)->orderBy('name')->findAll()]);
    }

    public function create()
    {
        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $p = $this->request->getPost();
        if ($p['end_date'] <= $p['start_date']) {
            return $this->fail('Tanggal selesai harus setelah tanggal mulai.');
        }

        $db = db_connect();
        $db->transStart();
        $uid = model(UserModel::class)->insert([
            'name' => $p['name'], 'email' => $p['email'],
            'password' => password_hash($p['password'], PASSWORD_DEFAULT), 'role' => 'siswa',
        ], true);
        model(StudentModel::class)->insert([
            'user_id' => $uid, 'mentor_id' => session('user_id'),
            'company_id' => $p['company_id'] ?: null,
            'nis' => $p['nis'], 'kelas' => $p['kelas'], 'jurusan' => $p['jurusan'],
            'start_date' => $p['start_date'], 'end_date' => $p['end_date'],
        ]);
        $db->transComplete();

        if (! $db->transStatus()) {
            return $this->fail('Gagal menyimpan data siswa.');
        }

        return redirect()->to('/students')->with('success', 'Siswa ditambahkan.');
    }

    public function edit(int $id)
    {
        $s = $this->mine($id);
        if (! $s) {
            return redirect()->to('/students')->with('error', 'Data tidak ditemukan.');
        }

        return view('students/form', ['s' => $s, 'companies' => model(CompanyModel::class)->orderBy('name')->findAll()]);
    }

    public function update(int $id)
    {
        $s = $this->mine($id);
        if (! $s) {
            return redirect()->to('/students')->with('error', 'Data tidak ditemukan.');
        }
        if (! $this->validate($this->rules((int) $s['user_id']))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $p = $this->request->getPost();
        if ($p['end_date'] <= $p['start_date']) {
            return $this->fail('Tanggal selesai harus setelah tanggal mulai.');
        }

        $user = ['name' => $p['name'], 'email' => $p['email']];
        if (! empty($p['password'])) {
            $user['password'] = password_hash($p['password'], PASSWORD_DEFAULT);
        }

        $db = db_connect();
        $db->transStart();
        model(UserModel::class)->update($s['user_id'], $user);
        model(StudentModel::class)->update($id, [
            'company_id' => $p['company_id'] ?: null,
            'nis' => $p['nis'], 'kelas' => $p['kelas'], 'jurusan' => $p['jurusan'],
            'start_date' => $p['start_date'], 'end_date' => $p['end_date'],
        ]);
        $db->transComplete();

        return redirect()->to('/students')->with('success', 'Data siswa diperbarui.');
    }

    public function delete(int $id)
    {
        $s = $this->mine($id);
        if ($s) {
            model(UserModel::class)->delete($s['user_id']); // students ikut terhapus (CASCADE)
        }

        return redirect()->to('/students')->with('success', 'Siswa dihapus.');
    }
}
