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
    public function index($username)
    {
        try {

            $user = User::where('name', $username)->value('id');

            if (!$user) {
                return response()->json(['message' => 'User Not Found'], 404);
            }

            $forms = User::with([
                'reference',
                'applicationForm',
                'attendanceTardiness',
                'confidentialityInformation',
                'criminalHistorySearch',
                'disclaimerWaiverLiability',
                'drugTestingPolicy',
                'employeeAgreement',
                'employeeConduct',
                'employeeDressCode',
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
                'universalPrecaution'
            ])->where('name', $username)->first();

            $cachedKey = 'user_forms_' . $username;
            $cachedForms = Cache::remember($cachedKey, 3600, function () use ($forms) {
                return $forms;
            });


            return response()->json($cachedForms, 200);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }
}
