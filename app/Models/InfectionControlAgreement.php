<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfectionControlAgreement extends Model
{
    use HasFactory;

    protected $table = 'infection_control_agreement';

    protected $fillable = [
        'applicant_id',
        'signature'
    ];
}
