<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisclaimerWaiverLiability extends Model
{
    use HasFactory;

    protected $table = 'disclaimer_waiver_liability'; 

    protected $fillable = [
        'applicant_id',
        'signature'
    ];
}
