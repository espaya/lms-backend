<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeHealthAide extends Model
{
    use HasFactory;

    protected $table = 'home_health_aide';

    protected $fillable = [
        'applicant_id',
        'signature'
    ];
}
