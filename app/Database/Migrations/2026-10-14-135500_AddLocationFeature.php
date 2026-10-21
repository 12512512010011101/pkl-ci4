<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Fitur absensi berbasis lokasi.
 *
 *  - companies   : titik lokasi PKL yang dikunci pembimbing (+ radius dalam meter)
 *  - attendances : koordinat, jarak ke lokasi PKL, dan akurasi GPS saat absen masuk/pulang
 *
 * latitude/longitude NULL = lokasi PKL belum dikunci.
 */
class AddLocationFeature extends Migration
{
    private function coord(): array
    {
        return ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true];
    }

    private function meters(): array
    {
        return ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true];
    }

    public function up()
    {
        $this->forge->addColumn('companies', [
            'latitude'  => $this->coord(),
            'longitude' => $this->coord(),
            'radius_m'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 100],
        ]);

        $this->forge->addColumn('attendances', [
            'check_in_lat'         => $this->coord(),
            'check_in_lng'         => $this->coord(),
            'check_in_distance_m'  => $this->meters(),
            'check_in_accuracy_m'  => $this->meters(),
            'check_out_lat'        => $this->coord(),
            'check_out_lng'        => $this->coord(),
            'check_out_distance_m' => $this->meters(),
            'check_out_accuracy_m' => $this->meters(),
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('attendances', [
            'check_in_lat', 'check_in_lng', 'check_in_distance_m', 'check_in_accuracy_m',
            'check_out_lat', 'check_out_lng', 'check_out_distance_m', 'check_out_accuracy_m',
        ]);
        $this->forge->dropColumn('companies', ['latitude', 'longitude', 'radius_m']);
    }
}
