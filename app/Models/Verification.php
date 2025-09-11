<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    use HasFactory;

    protected $table = 'verification';

    protected $fillable = [
        'disciplines',
        'licenseNumber',
        'expirationDate',
        'dateVerified',
        'licenseVerifiedBy',
        'actionOutstanding',
        'comments',
        'signature',
        'applicant_id'
    ];
}
