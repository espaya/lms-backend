<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmploymentApplication extends Model
{
    use HasFactory;

    protected $table = 'employments_applications';

    public function user()
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    protected $fillable = [
        'employee_hire_date',
        'SSN',
        'furnish_work',
        'employment_desired',
        'position',
        'date_start',
        'salary',
        'employed_now',
        'inqure_present_employer',
        'applied_before',
        'where',
        'when',
        'on_layoff_subject_to_recall',
        'travel_if_required',
        'relocate_if_required',
        'overtime_if_required',
        'attendance_requirements_position',
        'bonded',
        'convicted',
        'explain_convicted',
        'drivers_license',
        'drivers_license_state',
        'special_skills_qualifications',
        'applicant_id'
    ];
}
