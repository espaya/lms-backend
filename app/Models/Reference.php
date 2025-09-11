<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reference extends Model
{
    use HasFactory;

    protected $table = 'reference';

    protected $fillable = [
        'reference_name_1',
        'reference_address_1',
        'reference_phone_1',
        'reference_years_acquainted_1',
        'reference_name_2',
        'reference_address_2',
        'reference_phone_2',
        'reference_years_acquainted_2',
        'reference_name_3',
        'reference_address_3',
        'reference_phone_3',
        'reference_years_acquainted_3',
        'applicant_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }
}
