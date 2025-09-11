<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CriminalHistorySearch extends Model
{
    use HasFactory;

    protected $table = 'criminal_history_search';

    protected $fillable = [
        'signature',
        'applicant_id'
    ];
}
