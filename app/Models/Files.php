<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Files extends Model
{
    use HasFactory;

    protected $table = 'applicant_files';

    protected $fillable = [
        'social_security_card',
        'covid_proof',
        'pca_hha_certificate',
        'proof_ppd',
        'cpr_certificate',
        'applicant_id'
    ];
}
