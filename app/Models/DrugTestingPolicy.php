<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrugTestingPolicy extends Model
{
    use HasFactory;

    protected $table = 'drug_testing_policy';

    protected $fillable = [
        'applicant_id',
        'signature'
    ];
}
