<?php

namespace App\Controllers;

use App\Libraries\Geo;
use App\Models\AttendanceModel;
use App\Models\CompanyModel;

class Attendance extends PklController
{
    /**
     * false = mode "kunci saja" (standar saat ini): lokasi WAJIB terbaca (GPS harus menyala),
     *         jarak ke lokasi PKL hanya DICATAT. Absen tidak ditolak karena jarak maupun akurasi,
     *         jadi laptop/PC juga masih bisa absen.
     * true  = pembatasan aktif: absen DITOLAK bila di luar radius lokasi PKL atau akurasi GPS
     *         melebihi MAX_ACCURACY_M.
     * Dibuat public supaya view (_geo.php) dan JavaScript memakai aturan yang sama.
     */
    public const ENFORCE_RADIUS = false;

    /**
     * Akurasi GPS terburuk yang diterima (meter), hanya berlaku saat ENFORCE_RADIUS = true.
     * Lokasi dari IP / Wi-Fi di laptop biasanya jauh di atas angka ini.
     */
    public const MAX_ACCURACY_M = 300;

    /** true = siswa tidak bisa absen kalau pembimbing belum mengunci lokasi tempat PKL-nya. */
    public const REQUIRE_LOCKED_COMPANY = false;

    public function index()
    {
        $at = model(AttendanceModel::class);

        if ($this->isSiswa()) {
            $s       = $this->myStudent();
            $rows    = $s ? $at->where('student_id', $s['id'])->orderBy('date', 'DESC')->findAll() : [];
            $today   = $s ? $at->where('student_id', $s['id'])->where('date', date('Y-m-d'))->first() : null;
            $company = $this->companyOf($s);

            return view('attendance/index', ['rows' => $rows, 'today' => $today, 's' => $s, 'company' => $company]);
        }

        $rows = $at->select('attendances.*, users.name')
            ->join('students', 'students.id = attendances.student_id')
            ->join('users', 'users.id = students.user_id')
            ->whereIn('attendances.student_id', $this->myStudentIds())
            ->orderBy('attendances.date', 'DESC')->findAll(300);

        return view('attendance/index', ['rows' => $rows, 'today' => null, 's' => null, 'company' => null]);
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

        $loc = $this->checkLocation($this->companyOf($s));
        if (isset($loc['error'])) {
            return redirect()->back()->with('error', $loc['error']);
        }

        $at->insert([
            'student_id'          => $s['id'],
            'date'                => date('Y-m-d'),
            'check_in'            => date('H:i:s'),
            'status'              => 'hadir',
            'check_in_lat'        => $loc['lat'],
            'check_in_lng'        => $loc['lng'],
            'check_in_distance_m' => $loc['distance'],
            'check_in_accuracy_m' => $loc['accuracy'],
        ]);

        return redirect()->to('/attendance')->with('success', 'Absen masuk tercatat.' . $this->distanceNote($loc));
    }

    public function checkout()
    {
        $s   = $this->myStudent();
        $at  = model(AttendanceModel::class);
        $rec = $s ? $at->where('student_id', $s['id'])->where('date', date('Y-m-d'))->first() : null;

        if (! $rec || $rec['status'] !== 'hadir') {
            return redirect()->back()->with('error', 'Belum ada absen masuk hari ini.');
        }
        if (! empty($rec['check_out'])) {
            return redirect()->back()->with('error', 'Kamu sudah absen pulang hari ini.');
        }

        $loc = $this->checkLocation($this->companyOf($s));
        if (isset($loc['error'])) {
            return redirect()->back()->with('error', $loc['error']);
        }

        $at->update($rec['id'], [
            'check_out'            => date('H:i:s'),
            'check_out_lat'        => $loc['lat'],
            'check_out_lng'        => $loc['lng'],
            'check_out_distance_m' => $loc['distance'],
            'check_out_accuracy_m' => $loc['accuracy'],
        ]);

        return redirect()->to('/attendance')->with('success', 'Absen pulang tercatat.' . $this->distanceNote($loc));
    }

    // ------------------------------------------------------------------
    // Lokasi
    // ------------------------------------------------------------------

    /** Data tempat PKL milik siswa (null kalau belum punya). */
    private function companyOf(?array $s): ?array
    {
        if (! $s || empty($s['company_id'])) {
            return null;
        }

        return model(CompanyModel::class)->find($s['company_id']);
    }

    private function isLocked(?array $company): bool
    {
        return $company !== null && $company['latitude'] !== null && $company['longitude'] !== null;
    }

    /**
     * Membaca lokasi kiriman browser (lat, lng, accuracy) lalu menilainya.
     * Hasil: ['error' => '...'] atau ['lat', 'lng', 'accuracy', 'distance'].
     * Browser hanya melaporkan; keputusan selalu diambil di sini.
     */
    private function checkLocation(?array $company): array
    {
        if (self::REQUIRE_LOCKED_COMPANY && ! $this->isLocked($company)) {
            return ['error' => 'Pembimbing belum mengunci lokasi tempat PKL kamu. Hubungi pembimbing.'];
        }

        $lat = $this->request->getPost('lat');
        $lng = $this->request->getPost('lng');
        $acc = $this->request->getPost('accuracy');

        if (! is_numeric($lat) || ! is_numeric($lng)) {
            return ['error' => 'Lokasi tidak terbaca. Nyalakan lokasi (GPS), izinkan akses lokasi di browser, lalu coba lagi.'];
        }

        $lat      = (float) $lat;
        $lng      = (float) $lng;
        $accuracy = is_numeric($acc) ? (int) round((float) $acc) : null;

        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return ['error' => 'Koordinat lokasi tidak valid.'];
        }
        if (self::ENFORCE_RADIUS && $accuracy !== null && $accuracy > self::MAX_ACCURACY_M) {
            return ['error' => "Akurasi lokasi terlalu rendah (±{$accuracy} m). Nyalakan GPS dan coba di area terbuka, atau absen lewat HP."];
        }

        $distance = null;
        if ($this->isLocked($company)) {
            $distance = (int) round(Geo::distanceMeters($lat, $lng, (float) $company['latitude'], (float) $company['longitude']));
            $radius   = (int) ($company['radius_m'] ?: 100);

            if (self::ENFORCE_RADIUS && $distance > $radius) {
                return ['error' => "Kamu berada {$distance} m dari lokasi PKL. Absen hanya bisa dilakukan dalam radius {$radius} m."];
            }
        }

        return ['lat' => $lat, 'lng' => $lng, 'accuracy' => $accuracy, 'distance' => $distance];
    }

    private function distanceNote(array $loc): string
    {
        return $loc['distance'] !== null ? " (jarak ke lokasi PKL: {$loc['distance']} m)" : '';
    }
}
