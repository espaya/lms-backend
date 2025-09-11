<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NonCompeteAgreement extends Model
{
    use HasFactory;

    protected $table = 'non_compete_agreement';

    protected $fillable = [
        'applicant_id',
        'signature'
    ];

}
