<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminProfile extends Model
{
    use HasFactory;

    protected $table = 'admin_profile';

    protected $fillable = [
        'admin_fullname',
        'admin_phone',
        'admin_avatar',
        'admin_address'
    ];
}
