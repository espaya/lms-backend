<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AdminFormsController extends Controller
{
    /**/ 
    public function index(string $username)
    {
        try {
            // 1. Load all relations in ONE query instead of 23
            $user = User::with([
                'applicationForm.emergencyAddress',
                'applicationForm.presentAddress',
                'applicationForm.permanentAddress',
                'applicationForm.pastEmpInfo',
                'applicationForm.language',
                'applicationForm.academic',
                'applicationForm.reference',
                'applicationForm.signature',
                'applicationForm.profile',
                'attendanceTardiness',
                'confidentialityInformation',
                'criminalHistorySearch',
                'disclaimerWaiverLiability',
                'drugTestingPolicy',
                'employeeAgreement',
                'employeeConduct',
                'employeeDressCode',
                'employeeHandbook',
                'employeeOrientation',
                'employeeReferenceCheck',
                'employeeSafety',
                'healthSafetyAgreement',
                'homeHealthAide',
                'infectionControl',
                'nonCompeteAgreement',
                'policyProcedure',
                'reporting',
                'sexualHarassment',
                'smoking',
                'swornDisclosure',
                'universalPrecaution',
                'verification',
            ])->where('name', $username)->first();

            if (!$user) {
                return response()->json(['message' => 'User Not Found'], 404);
            }

            // 2. Map the data to the exact keys the React frontend expects
            return response()->json([
                'application_form' => $user->applicationForm,
                'attendance_tardiness' => $user->attendanceTardiness,
                'confidentiality_information' => $user->confidentialityInformation,
                'criminal_history_search' => $user->criminalHistorySearch,
                'disclaimer_waiver_liability' => $user->disclaimerWaiverLiability,
                'drug_testing_policy' => $user->drugTestingPolicy,
                'employee_agreement' => $user->employeeAgreement,
                'employee_conduct' => $user->employeeConduct,
                'employee_dress_code' => $user->employeeDressCode,
                'employee_handbook' => $user->employeeHandbook,
                'employee_orientation' => $user->employeeOrientation,
                'employee_reference_check' => $user->employeeReferenceCheck,
                'employee_safety' => $user->employeeSafety,
                'health_safety_agreement' => $user->healthSafetyAgreement,
                'home_health_aide' => $user->homeHealthAide,
                'infection_control' => $user->infectionControl,
                'non_compete_agreement' => $user->nonCompeteAgreement,
                'policy_procedure' => $user->policyProcedure,
                'reporting' => $user->reporting,
                'sexual_harassment' => $user->sexualHarassment,
                'smoking' => $user->smoking,
                'sworn_disclosure' => $user->swornDisclosure,
                'universal_precaution' => $user->universalPrecaution,
                'verification' => $user->verification,
            ], 200);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }
}
