<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UniversalPrecautions extends Model
{
    use HasFactory;

    protected $table = 'universal_precaution';

    protected $fillable = [
        'signature',
        'applicant_id'
    ];
}
