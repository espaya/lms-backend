<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceTardiness extends Model
{
    use HasFactory;

    protected $table = 'attendance_tardiness';

    protected $fillable = [
        'applicant_id',
        'signature'
    ];
}
