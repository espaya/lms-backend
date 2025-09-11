<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delete extends Model
{
    use HasFactory;

    protected $table = 'request_delete';

    protected $fillable = [
        'applicant_id'
    ];
}
