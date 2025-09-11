<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SexualHarassment extends Model
{
    use HasFactory;

    protected $table = 'sexual_harassment';

    protected $fillable = [
        'applicant_id',
        'signature'
    ];
}
