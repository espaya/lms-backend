<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgencyNotes extends Controller
{
    public function agencyManagementNotes($applicant_id, $id)
    {
        $notes = DB::table('agency_notes')->where('applicant_id', $applicant_id)->where('application_form_id', $id)->first();
        $notesData = [];
        foreach($notes as $item)
        {
            $notesData[] = [
                'agency_management_notes' => $item->agency_management_notes,
                'applicant_id' => $item->applicant_id,
                'application_form_id' => $item->application_form_id
            ];
        }

        return $notesData;
    }
}
