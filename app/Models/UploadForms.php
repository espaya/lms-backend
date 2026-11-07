<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadForms extends Model
{
    protected $table = 'upload_forms';

    protected $fillable = [
        'form_name',
        'document_path',
    ];
}
