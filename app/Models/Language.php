<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected $table = 'language';

    protected $fillable = [
        'language_1',
        'read_write_1',
        'read_speak_1',
        'speak_only_1',
        'language_2',
        'read_write_2',
        'read_speak_2',
        'speak_only_2',
        'applicant_id'
    ];
}
