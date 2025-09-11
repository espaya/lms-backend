<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PastEmpInfo extends Model
{
    use HasFactory;

    protected $table = 'past_employment_info';

    protected $fillable = [
        'from_date_1',
        'to_date_1',
        'name_address_employer_1',
        'phone_number_1',
        'job_1',
        'salary_1',
        'reason_leaving_1',

        'from_date_2',
        'to_date_2',
        'name_address_employer_2',
        'phone_number_2',
        'job_2',
        'salary_2',
        'reason_leaving_2',

        'from_date_3',
        'to_date_3',
        'name_address_employer_3',
        'phone_number_3',
        'job_3',
        'salary_3',
        'reason_leaving_3',
        'applicant_id'
    ];
}
