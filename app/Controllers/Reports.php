<?php

namespace App\Controllers;

use App\Models\StudentModel;

class Reports extends PklController
{
    private function rows(): array
    {
        return model(StudentModel::class)->detail()->where('students.mentor_id', session('user_id'))->orderBy('users.name')->findAll();
    }

    public function index()
    {
        return view('reports/index', ['rows' => $this->rows()]);
    }

    public function export()
    {
        $out = fopen('php://temp', 'r+');
        fputcsv($out, ['Nama', 'NIS', 'Kelas', 'Jurusan', 'Tempat PKL', 'Mulai', 'Selesai', 'Hadir', 'Izin', 'Sakit', 'Jurnal Disetujui', 'Nilai Akhir'], ',', '"', '');
        foreach ($this->rows() as $r) {
            fputcsv($out, [
                $r['name'], $r['nis'], $r['kelas'], $r['jurusan'], $r['company_name'], $r['start_date'], $r['end_date'],
                $r['hadir'], $r['izin'], $r['sakit'], $r['jurnal_ok'], $r['nilai'] ?? '-',
            ], ',', '"', '');
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="rekap-pkl-' . date('Ymd') . '.csv"')
            ->setBody("\xEF\xBB\xBF" . $csv);
    }
}
