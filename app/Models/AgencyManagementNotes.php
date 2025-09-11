<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgencyManagementNotes extends Model
{
    use HasFactory;

    protected $table = 'agency_notes';

    protected $fillable = [
        'agency_managements_notes',
        'applicant_id',
        'application_form_id'
    ];
}
