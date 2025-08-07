<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['name', 'slug'];

    public function topics()
    {
        return $this->hasMany(Topic::class);
    }
}
