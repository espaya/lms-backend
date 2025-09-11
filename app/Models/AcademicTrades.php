<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicTrades extends Model
{
    use HasFactory;

    protected $table = 'academic_trades_business';

    public function user()
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    protected $fillable = [
        'edu_current_name_location_school',
        'edu_current_number_years',
        'edu_current_did_graduate',
        'edu_current_subjects_studied',
        'edu_last_name_location_school',
        'edu_last_number_years',
        'edu_last_did_graduate',
        'edu_last_subjects_studied',
        'trades_current_name_location_school',
        'trades_current_number_years',
        'trades_current_did_graduate',
        'trades_current_subjects_studied',
        'trades_last_current_name_location_school',
        'trades_last_current_number_years',
        'trades_last_current_did_graduate',
        'trades_last_subjects_studied',
        'applicant_id'
    ];
}
