<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeHandbook extends Model
{
    protected $fillable = [
        'applicant_id',
        'signature',
    ];

    protected $table = 'employee_handbook';
}
