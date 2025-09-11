<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeOrientation extends Model
{
    use HasFactory;

    protected $table = 'employee_orientation';

    protected $fillable = [
        'signature',
        'dateOfOrientation',
        'applicant_id'
    ];
}
