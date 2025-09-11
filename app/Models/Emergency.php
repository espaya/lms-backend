<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emergency extends Model
{
    use HasFactory;

    protected $table = 'emergency';

    protected $fillable = [
        'emergency_address',
        'emergency_city',
        'emergency_state',
        'emergency_zip',
        'applicant_id'
    ];
}
