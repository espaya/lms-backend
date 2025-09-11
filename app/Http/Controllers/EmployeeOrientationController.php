<?php

namespace App\Http\Controllers;

use App\Models\EmployeeOrientation;
use App\Models\EmploymentApplication;
use App\Services\UserProfileService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class EmployeeOrientationController extends Controller
{
    protected $userProfileService;

    public function __construct(UserProfileService $userProfileService)
    {
        $this->userProfileService = $userProfileService;
    }

    public function index(UserProfileService $userProfileService)
    {
        $userID = Auth::id();

        try {
            $profileData = $userProfileService->getUserProfileData();
            $empApp = EmploymentApplication::where('applicant_id', $userID)->first();
            $employeeOrientation = EmployeeOrientation::where('applicant_id', $userID)->first();

            return response()->json([
                'empApp' => $empApp,
                'employeeOrientation' => $employeeOrientation,
                'profileData' => $profileData->full_name
            ], 200);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }

    public function store(Request $request)
    {
        $userID = Auth::id();

        $request->validate([
            'signature' => 'required',
            'dateOfOrientation' => 'required'
        ], [
            'signature.required' => 'This field is required',
            'dateOfOrientation.required' => 'This field is required'
        ]);

        DB::beginTransaction();

        try {
            $empOrient = new EmployeeOrientation();

            // get signature input
            $signatureData = $request->input('signature');

            $signatureParts = explode(',', $signatureData);
            $signatureEncoded = $signatureParts[1]; // Extract the base64-encoded part

            $signatureBinary = base64_decode($signatureEncoded);

            // Generate a unique filename for the signature file
            $signatureName = time() . '.png';

            // Save the signature file to disk using the Storage facade
            // Storage::put('public/signature/' . $signatureName, $signatureBinary);
            $singaturePath = storage_path('app/public/signature');


            $empOrient->signature = $signatureName;
            $empOrient->applicant_id = $userID;
            $empOrient->dateOfOrientation = $request->dateOfOrientation;

            EmployeeOrientation::firstOrCreate(
                ['applicant_id' => $userID],
                [
                    'signature' => $signatureName,
                    'applicant_id' => $userID,
                    'dateOfOrientation' => $request->dateOfOrientation
                ]
            );

            DB::commit();

            if (!File::exists($singaturePath)) {
                File::makeDirectory($singaturePath, 0755, true);
            }

            Storage::disk('public')->put('signature/' . $signatureName, $signatureBinary);

            return response()->json(['message' => 'Employee Orientation Signed Successfully'], 200);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }
}
