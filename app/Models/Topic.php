<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Topic extends Model
{
    use SoftDeletes;

    protected $fillable = ['subject_id', 'name', 'slug', 'fileName', 'notified_at'];

    protected $casts = ['notified_at', 'datetime'];

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
