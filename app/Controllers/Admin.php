<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\StudentModel;
use App\Models\UserModel;

/**
 * Fitur khusus admin: rekap semua siswa, kelola akun, kelola data siswa.
 * Semua route ke controller ini dijaga filter 'auth:admin' di Routes.php.
 */
class Admin extends PklController
{
    // ------------------------------------------------------------------
    // Rekap semua siswa
    // ------------------------------------------------------------------

    /** Query semua siswa + nama pembimbing (lanjutkan dengan ->where / ->findAll). */
    private function allStudents()
    {
        return model(StudentModel::class)->detail()
            ->select('mentor.name AS mentor_name', false)
            ->join('users mentor', 'mentor.id = students.mentor_id', 'left');
    }

    public function recap()
    {
        return view('admin/recap', ['rows' => $this->allStudents()->orderBy('users.name')->findAll()]);
    }

    public function exportRecap()
    {
        $rows = $this->allStudents()->orderBy('users.name')->findAll();

        $fh = fopen('php://temp', 'r+');
        fputcsv($fh, ['Nama', 'Email', 'NIS', 'Kelas', 'Jurusan', 'Tempat PKL', 'Pembimbing', 'Mulai', 'Selesai',
            'Progres (%)', 'Hadir', 'Izin', 'Sakit', 'Jurnal disetujui', 'Jurnal total', 'Nilai akhir']);

        foreach ($rows as $r) {
            fputcsv($fh, array_map([$this, 'csvCell'], [
                $r['name'], $r['email'], $r['nis'], $r['kelas'], $r['jurusan'],
                $r['company_name'] ?? '', $r['mentor_name'] ?? '', $r['start_date'], $r['end_date'],
                StudentModel::progress($r), $r['hadir'], $r['izin'], $r['sakit'],
                $r['jurnal_ok'], $r['jurnal_all'], $r['nilai'] ?? '',
            ]));
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="rekap-siswa-' . date('Ymd') . '.csv"')
            ->setBody("\xEF\xBB\xBF" . $csv); // BOM supaya Excel membaca UTF-8
    }

    /** Mencegah CSV injection: sel yang diawali = + - @ dianggap rumus oleh Excel. */
    private function csvCell($v): string
    {
        $v = (string) $v;

        return $v !== '' && strpbrk($v[0], "=+-@\t\r") !== false ? "'" . $v : $v;
    }

    // ------------------------------------------------------------------
    // Akun pembimbing dan admin
    // ------------------------------------------------------------------

    private function userRules(?int $id = null, bool $withRole = true): array
    {
        $r = [
            'name'     => 'required|min_length[3]|max_length[100]',
            'email'    => 'required|valid_email|max_length[150]|' . ($id ? "is_unique[users.email,id,{$id}]" : 'is_unique[users.email]'),
            'password' => $id ? 'permit_empty|min_length[6]' : 'required|min_length[6]',
        ];
        if ($withRole) {
            $r['role'] = 'required|in_list[pembimbing,admin]';
        }

        return $r;
    }

    public function users()
    {
        $role = (string) $this->request->getGet('role');
        $q    = model(UserModel::class)
            ->select('users.id, users.name, users.email, users.role, users.created_at,
                (SELECT s.id FROM students s WHERE s.user_id = users.id) AS student_id', false)
            ->orderBy('users.role')->orderBy('users.name');

        if (in_array($role, ['siswa', 'pembimbing', 'admin'], true)) {
            $q->where('users.role', $role);
        } else {
            $role = '';
        }

        return view('admin/users', ['rows' => $q->findAll(), 'role' => $role]);
    }

    public function addUser()
    {
        return view('admin/user_form', ['u' => null]);
    }

    public function createUser()
    {
        if (! $this->validate($this->userRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $p = $this->request->getPost();

        model(UserModel::class)->insert([
            'name'     => $p['name'],
            'email'    => $p['email'],
            'password' => password_hash($p['password'], PASSWORD_DEFAULT),
            'role'     => $p['role'],
        ]);

        return redirect()->to('/admin/users')->with('success', 'Akun ditambahkan.');
    }

    public function editUser(int $id)
    {
        $u = model(UserModel::class)->find($id);
        if (! $u) {
            return redirect()->to('/admin/users')->with('error', 'Akun tidak ditemukan.');
        }
        if ($u['role'] === 'siswa') {
            return redirect()->to('/admin/students')->with('error', 'Akun siswa diubah lewat halaman Siswa.');
        }

        return view('admin/user_form', ['u' => $u]);
    }

    public function updateUser(int $id)
    {
        $model = model(UserModel::class);
        $u     = $model->find($id);
        if (! $u) {
            return redirect()->to('/admin/users')->with('error', 'Akun tidak ditemukan.');
        }
        if ($u['role'] === 'siswa') {
            return redirect()->to('/admin/students')->with('error', 'Akun siswa diubah lewat halaman Siswa.');
        }

        // Admin tidak boleh mengubah peran akunnya sendiri (supaya tidak terkunci keluar).
        $isSelf = $id === (int) session('user_id');
        if (! $this->validate($this->userRules($id, ! $isSelf))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $p = $this->request->getPost();

        $data = ['name' => $p['name'], 'email' => $p['email']];
        if (! empty($p['password'])) {
            $data['password'] = password_hash($p['password'], PASSWORD_DEFAULT);
        }
        if (! $isSelf) {
            $data['role'] = $p['role'];
        }
        $model->update($id, $data);

        if ($isSelf) {
            session()->set('name', $data['name']);
        }

        return redirect()->to('/admin/users')->with('success', 'Akun diperbarui.');
    }

    public function deleteUser(int $id)
    {
        if ($id === (int) session('user_id')) {
            return redirect()->to('/admin/users')->with('error', 'Kamu tidak bisa menghapus akunmu sendiri.');
        }

        $model = model(UserModel::class);
        $u     = $model->find($id);
        if (! $u) {
            return redirect()->to('/admin/users')->with('error', 'Akun tidak ditemukan.');
        }
        if ($u['role'] === 'siswa') {
            return redirect()->to('/admin/users')->with('error', 'Akun siswa dihapus lewat halaman Siswa.');
        }

        // Siswa bimbingan tidak ikut terhapus: mentor_id mereka jadi kosong (FK SET NULL).
        $model->delete($id);

        return redirect()->to('/admin/users')->with('success', 'Akun dihapus.');
    }

    // ------------------------------------------------------------------
    // Data siswa (semua siswa)
    // ------------------------------------------------------------------

    private function studentRules(?int $userId = null): array
    {
        return [
            'name'       => 'required|min_length[3]|max_length[100]',
            'email'      => 'required|valid_email|max_length[150]|' . ($userId ? "is_unique[users.email,id,{$userId}]" : 'is_unique[users.email]'),
            'password'   => $userId ? 'permit_empty|min_length[6]' : 'required|min_length[6]',
            'nis'        => 'required|max_length[30]',
            'kelas'      => 'required|max_length[30]',
            'jurusan'    => 'required|max_length[80]',
            'mentor_id'  => 'permit_empty|is_natural_no_zero',
            'company_id' => 'permit_empty|is_natural_no_zero',
            'start_date' => 'required|valid_date',
            'end_date'   => 'required|valid_date',
        ];
    }

    private function studentFormData(?array $s): array
    {
        return [
            's'         => $s,
            'mentors'   => model(UserModel::class)->where('role', 'pembimbing')->orderBy('name')->findAll(),
            'companies' => model(CompanyModel::class)->orderBy('name')->findAll(),
        ];
    }

    /** Memeriksa tanggal, pembimbing, dan tempat PKL kiriman form. Mengembalikan pesan error atau null. */
    private function checkStudentInput(array $p): ?string
    {
        if ($p['end_date'] <= $p['start_date']) {
            return 'Tanggal selesai harus setelah tanggal mulai.';
        }
        if (! empty($p['mentor_id']) && ! model(UserModel::class)->where('id', $p['mentor_id'])->where('role', 'pembimbing')->first()) {
            return 'Pembimbing yang dipilih tidak valid.';
        }
        if (! empty($p['company_id']) && ! model(CompanyModel::class)->find($p['company_id'])) {
            return 'Tempat PKL yang dipilih tidak valid.';
        }

        return null;
    }

    public function students()
    {
        $q      = $this->allStudents()->orderBy('users.name');
        $belum  = $this->request->getGet('belum') === '1';
        if ($belum) {
            $q->groupStart()->where('students.mentor_id', null)->orWhere('students.company_id', null)->groupEnd();
        }

        return view('admin/students', ['rows' => $q->findAll(), 'belum' => $belum]);
    }

    public function addStudent()
    {
        return view('admin/student_form', $this->studentFormData(null));
    }

    public function createStudent()
    {
        if (! $this->validate($this->studentRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $p = $this->request->getPost();
        if ($err = $this->checkStudentInput($p)) {
            return $this->fail($err);
        }

        $db = db_connect();
        $db->transStart();
        $uid = model(UserModel::class)->insert([
            'name' => $p['name'], 'email' => $p['email'],
            'password' => password_hash($p['password'], PASSWORD_DEFAULT), 'role' => 'siswa',
        ], true);
        model(StudentModel::class)->insert([
            'user_id' => $uid, 'mentor_id' => $p['mentor_id'] ?: null, 'company_id' => $p['company_id'] ?: null,
            'nis' => $p['nis'], 'kelas' => $p['kelas'], 'jurusan' => $p['jurusan'],
            'start_date' => $p['start_date'], 'end_date' => $p['end_date'],
        ]);
        $db->transComplete();

        if (! $db->transStatus()) {
            return $this->fail('Gagal menyimpan data siswa.');
        }

        return redirect()->to('/admin/students')->with('success', 'Siswa ditambahkan.');
    }

    public function editStudent(int $id)
    {
        $s = $this->allStudents()->where('students.id', $id)->first();
        if (! $s) {
            return redirect()->to('/admin/students')->with('error', 'Data tidak ditemukan.');
        }

        return view('admin/student_form', $this->studentFormData($s));
    }

    public function updateStudent(int $id)
    {
        $s = $this->allStudents()->where('students.id', $id)->first();
        if (! $s) {
            return redirect()->to('/admin/students')->with('error', 'Data tidak ditemukan.');
        }
        if (! $this->validate($this->studentRules((int) $s['user_id']))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $p = $this->request->getPost();
        if ($err = $this->checkStudentInput($p)) {
            return $this->fail($err);
        }

        $user = ['name' => $p['name'], 'email' => $p['email']];
        if (! empty($p['password'])) {
            $user['password'] = password_hash($p['password'], PASSWORD_DEFAULT);
        }

        $db = db_connect();
        $db->transStart();
        model(UserModel::class)->update($s['user_id'], $user);
        model(StudentModel::class)->update($id, [
            'mentor_id' => $p['mentor_id'] ?: null, 'company_id' => $p['company_id'] ?: null,
            'nis' => $p['nis'], 'kelas' => $p['kelas'], 'jurusan' => $p['jurusan'],
            'start_date' => $p['start_date'], 'end_date' => $p['end_date'],
        ]);
        $db->transComplete();

        if (! $db->transStatus()) {
            return $this->fail('Gagal memperbarui data siswa.');
        }

        return redirect()->to('/admin/students')->with('success', 'Data siswa diperbarui.');
    }

    public function deleteStudent(int $id)
    {
        $s = model(StudentModel::class)->find($id);
        if ($s) {
            model(UserModel::class)->delete($s['user_id']); // students, absensi, jurnal, dll. ikut terhapus (CASCADE)
        }

        return redirect()->to('/admin/students')->with('success', 'Siswa dihapus.');
    }
}
