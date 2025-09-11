<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSafety extends Model
{
    use HasFactory;

    protected $table = "employee_safety";
    
    protected $fillable = [
        'signature',
        'applicant_id'
    ];
}
