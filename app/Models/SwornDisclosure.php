<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SwornDisclosure extends Model
{
    use HasFactory;

    protected $table = 'sworn_disclosure_statement';

    protected $fillable = [
        'applicant_id',
        'mailing_address',
        'convicted_outside_commonwealth',
        'outside_commonwealth_specify',
        'convicted_pending',
        'convicted_pending_specify',
        'child_abuse',
        'signature',
        'wit_signature'
    ];
}
