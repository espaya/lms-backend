<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PremanentAddress extends Model
{
    use HasFactory;

    protected $table = 'permanent_address';

    protected $fillable = [
        'permanent_address',
        'permanent_city',
        'permanent_state',
        'permanent_zip',
        'applicant_id'
    ];
}
