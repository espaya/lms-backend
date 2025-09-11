<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAgreement extends Model
{
    use HasFactory;

    protected $table = 'employee_agreement';

    protected $fillable = [
        'monday_hour',
        'tuesday_hour',
        'wednesday_hour',
        'thursday_hour',
        'friday_hour',
        'saturday_hour',
        'sunday_hour',
        'time_off',
        'pay_per_hour',
        'other_agreements',
        'signature',
        'applicant_id'
    ];
}
