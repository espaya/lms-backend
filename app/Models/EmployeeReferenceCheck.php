<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeReferenceCheck extends Model
{
    use HasFactory;

    protected $table = 'employee_reference_check';

    protected $fillable = [
        'applicant_id',
        'company_contacted',
        'employer_name',
        'from_date',
        'to_date',
        'eligible_for_hire',
        'comments',
        'received_by',
        'name_of_company',
        'signature',
        'rep_signature',
        'rep_title',
        'company_signature'
    ];
}
