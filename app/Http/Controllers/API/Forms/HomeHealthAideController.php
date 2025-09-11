<?php

namespace App\Http\Controllers;

use App\Models\HomeHealthAide;
use App\Services\UserProfileService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HomeHealthAideController extends Controller
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

            $homeHealth = HomeHealthAide::where('applicant_id', $userID)->get();

            return view([
                'homeHealthData' => $homeHealth,
                'profileData' => $profileData
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
            'signature' => 'required'
        ], [
            'signature.required' => 'This field is required'
        ]);


        DB::beginTransaction();

        try {
            $homeHealth = new HomeHealthAide();

            // get signature input
            $signatureData = $request->input('signature');

            $signatureParts = explode(',', $signatureData);
            $signatureEncoded = $signatureParts[1]; // Extract the base64-encoded part

            $signatureBinary = base64_decode($signatureEncoded);

            // Generate a unique filename for the signature file
            $signatureName = time() . '.png';

            // Save the signature file to disk using the Storage facade
            Storage::put('public/signature/' . $signatureName, $signatureBinary);

            $homeHealth->signature = $signatureName;
            $homeHealth->applicant_id = $userID;

            HomeHealthAide::firstOrCreate(
                ['applicant_id' => $userID],
                [
                    'signature' => $signatureName,
                    'applicant_id' => $userID
                ]
            );

            return response()->json(['message' => 'Job Description: Home Health Aide Signed Successfully'], 200);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());
            return response()->json(['message' => 'An unexpected error occurred']);
        }
    }
}
