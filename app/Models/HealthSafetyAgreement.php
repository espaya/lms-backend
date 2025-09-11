<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthSafetyAgreement extends Model
{
    use HasFactory;

    protected $table = 'health_safety_agreement';

    protected $fillable = [
        'signature',
        'applicant_id'
    ];
}
