<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Models\AgencyManagementNotes;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AgencyNotes extends Controller
{
    public function agencyManagementNotes($applicant_id, $id)
    {
        try {
            $notes = AgencyManagementNotes::where('applicant_id', $applicant_id)
                ->where('application_form_id', $id)
                ->first();

            $notesData = [];

            foreach ($notes as $item) {
                $notesData[] = [
                    'agency_management_notes' => $item->agency_management_notes,
                    'applicant_id' => $item->applicant_id,
                    'application_form_id' => $item->application_form_id
                ];
            }

            return $notesData;
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
        }
    }
}
