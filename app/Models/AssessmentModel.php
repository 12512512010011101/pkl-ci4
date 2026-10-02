<?php

namespace App\Models;

use CodeIgniter\Model;

class AssessmentModel extends Model
{
    protected $table         = 'assessments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['student_id', 'disiplin', 'keterampilan', 'sikap', 'laporan', 'final_score', 'note'];
}
