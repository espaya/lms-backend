<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Smoking extends Model
{
    use HasFactory;

    protected $table = 'smoking_in_the_workplace';

    protected $fillable = [
        'applicant_id',
        'signature',
        'supervisor_signature',
        'hr_signature'
    ];
}
