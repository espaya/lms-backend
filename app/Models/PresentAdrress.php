<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresentAdrress extends Model
{
    use HasFactory;

    protected $table = 'present_address';

    protected $fillable = [
        'present_address',
        'present_city',
        'present_state',
        'present_zip',
        'applicant_id'
    ];
}
