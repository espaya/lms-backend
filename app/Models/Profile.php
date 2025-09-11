<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $table = 'users_profile';

    protected $fillable = [
        'full_name',
        'phone',
        'user_avatar',
        'applicant_id'
    ];
}
