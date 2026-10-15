<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table         = 'attendances';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'student_id', 'date', 'check_in', 'check_out', 'status', 'note',
        'check_in_lat', 'check_in_lng', 'check_in_distance_m', 'check_in_accuracy_m',
        'check_out_lat', 'check_out_lng', 'check_out_distance_m', 'check_out_accuracy_m',
    ];
}
