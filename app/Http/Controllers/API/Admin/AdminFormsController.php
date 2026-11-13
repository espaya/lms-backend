<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;  // ⬅ Add this line here
use App\Models\UploadForms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

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

    /* Upload Forms */
    public function uploadForms(Request $request)
    {
        $request->validate([
            'form_name' => 'required|string',
            'document'  => 'required|mimes:doc,docx,pdf|max:10240', // 10MB max
        ], [
            'form_name.required' => 'Form name is required',
            'form_name.string' => 'Form name must be a string',
            'document.required' => 'Document is required',
            'document.mimes' => 'Document must be a Word file (doc or docx) or PDF',
            'document.max' => 'Document size must not exceed 10MB',
        ]);

        if (!$request->hasFile('document')) {
            return response()->json(['message' => 'No document uploaded'], 400);
        }

        $uploadedFilePath = null;

        DB::beginTransaction();

        try {
            $file = $request->file('document');

            // 2. Ensure the directory exists
            $uploadDir = storage_path('app/public/forms');
            if (!File::exists($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true, true);
            }

            // 3. Generate safe filename
            $filename = time() . '_' . Str::slug($request->form_name) . '.' . $file->getClientOriginalExtension();

            // 4. Save the file
            $file->storeAs('forms', $filename, 'public');

            // Keep track of the relative path for cleanup
            $uploadedFilePath = 'forms/' . $filename;

            // 5. Save to Database
            UploadForms::create([
                'form_name'     => $request->form_name,
                'document_path' => $uploadedFilePath
            ]);

            DB::commit();

            return response()->json(['message' => 'Uploaded successfully'], 200);
        } catch (Exception $ex) {
            // 6. Rollback Database
            DB::rollBack();

            // 7. Delete the uploaded file if it exists
            if ($uploadedFilePath && File::exists(storage_path('app/public/' . $uploadedFilePath))) {
                File::delete(storage_path('app/public/' . $uploadedFilePath));
            }

            Log::error('Form upload failed: ' . $ex->getMessage());

            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }

    /* Get All uploaded Forms */
    public function getAllForms(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);

            $forms = UploadForms::orderBy('id', 'DESC')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $forms->items(),
                'pagination' => [
                    'total' => $forms->total(),
                    'per_page' => $forms->perPage(),
                    'current_page' => $forms->currentPage(),
                    'last_page' => $forms->lastPage(),
                ]
            ], 200);
        } catch (\Exception $ex) {
            Log::error('Error getting forms: ' . $ex->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving forms'
            ], 500);
        }
    }

    /**/
    public function isAllFormsSigned()
    { 
        try {
            // List all the relationships that require a signature
            $relations = [
                'applicationForm.signature',
                'attendanceTardiness.signature',
                'confidentialityInformation.signature',
                'criminalHistorySearch.signature',
                'disclaimerWaiverLiability.signature',
                'drugTestingPolicy.signature',
                'employeeAgreement.signature',
                'employeeConduct.signature',
                'employeeDressCode.signature',
                'employeeHandbook.signature',
                'employeeOrientation.signature',
                'employeeReferenceCheck.signature',
                'employeeSafety.signature',
                'healthSafetyAgreement.signature',
                'homeHealthAide.signature',
                'infectionControl.signature',
                'nonCompeteAgreement.signature',
                'policyProcedure.signature',
                'reporting.signature',
                'sexualHarassment.signature',
                'smoking.signature',
                'swornDisclosure.signature',
                'universalPrecaution.signature',
                'verification.signature',
            ];

            $user = User::with($relations)->find(Auth::id());

            if (!$user) {
                return response()->json(['message' => 'User Not Found'], 404);
            }

            // Check if EVERY relationship has a signature (is not null)
            $allSigned = collect($relations)->every(function ($relation) use ($user) {
                // We use data_get() to safely traverse the dot-notation relationship
                return data_get($user, $relation) !== null;
            });

            return response()->json(['signed' => $allSigned], 200);
        } catch (Exception $ex) {
            Log::error('Error checking signatures: ' . $ex->getMessage());
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }
}
