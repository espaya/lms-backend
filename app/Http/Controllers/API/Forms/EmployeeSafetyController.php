<?php

namespace App\Http\Controllers;

use App\Models\EmployeeSafety;
use App\Services\UserProfileService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EmployeeSafetyController extends Controller
{

    protected $userProfileService;

    public function __construct(UserProfileService $userProfileService)
    {
        $this->middleware('auth');
        $this->userProfileService = $userProfileService;
    }

    public function index(UserProfileService $userProfileService)
    {
        $userID = Auth::id();
        try {
            // profile avatar
            $profileData = $userProfileService->getUserProfileData();

            $empSafety = EmployeeSafety::where('applicant_id', $userID)->get();

            return response()->json(
                [
                    'empSafety' => $empSafety,
                    'profileData' => $profileData
                ],
                200
            );
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }

    public function store(Request $request)
    {
        // authenticated user
        $userID = Auth::id();

        // validate input
        $request->validate([
            'signature' => 'required',
        ], [
            'signature.required' => 'This field is required'
        ]);

        DB::beginTransaction();

        try {
            // employee safety oobject
            $employeeSafety = new EmployeeSafety();

            // get signature input
            $signatureData = $request->input('signature');

            $signatureParts = explode(',', $signatureData);
            $signatureEncoded = $signatureParts[1]; // Extract the base64-encoded part

            $signatureBinary = base64_decode($signatureEncoded);

            // Generate a unique filename for the signature file
            $signatureName = time() . '.png';

            // Save the signature file to disk using the Storage facade
            Storage::put('public/signature/' . $signatureName, $signatureBinary);

            $employeeSafety->signature = $signatureName;
            $employeeSafety->applicant_id = $userID;

            EmployeeSafety::firstOrCreate(
                ['applicant_id' => $userID],
                [
                    'signature' => $signatureName,
                    'applicant_id' => $userID
                ]
            );

            DB::commit();

            // refresh page when form is submitted
            return response()->json(['message' => 'Employee Safety Cellular Phone Use Signed Successfully!'], 200);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }
}
