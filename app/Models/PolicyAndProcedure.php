<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicyAndProcedure extends Model
{
    use HasFactory;

    protected $table = 'policy_and_procedure';

    protected $fillable = [
        'signature',
        'applicant_id'
    ];
}
