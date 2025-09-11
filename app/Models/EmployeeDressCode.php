<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDressCode extends Model
{
    use HasFactory;

    protected $table = 'employee_dress_code';

    protected $fillable = [
        'signature',
        'applicant_id'
    ];
}
