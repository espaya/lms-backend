<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeConduct extends Model
{
    use HasFactory;

    protected $table = 'employee_conduct';

    protected $fillable = [
        'applicant_id',
        'signature'
    ];
}
