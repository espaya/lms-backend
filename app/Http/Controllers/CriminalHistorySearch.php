<?php

namespace App\Http\Controllers;

use App\Models\CriminalHistorySearch as ModelsCriminalHistorySearch;
use App\Models\Profile;
use App\Services\UserProfileService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class CriminalHistorySearch extends Controller
{
    protected $userProfileService;

    public function __construct(UserProfileService $userProfileService)
    {
        $this->userProfileService = $userProfileService;
    }

    public function index(UserProfileService $userProfileService)
    {
        // authenticated user
        $userID = Auth::id();

        try {
            $profileData = $userProfileService->getUserProfileData();

            // db query
            $criminalHistory = ModelsCriminalHistorySearch::where('applicant_id', $userID)->first();

            return response()->json([
                'criminalHistory' => $criminalHistory,
                'profileData' => $profileData ?? 'N/A'
            ], 200);
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
            'signature' => 'required'
        ], [
            'signature.required' => 'This field is required'
        ]);

        DB::beginTransaction();

        try {
            $criminalHistory = new ModelsCriminalHistorySearch();

            // get signature input
            $signatureData = $request->input('signature');

            $signatureParts = explode(',', $signatureData);
            $signatureEncoded = $signatureParts[1]; // Extract the base64-encoded part

            $signatureBinary = base64_decode($signatureEncoded);

            // Generate a unique filename for the signature file
            $signatureName = time() . '.png';

            // Save the signature file to disk using the Storage facade
            // Storage::put('public/signature/' . $signatureName, $signatureBinary);
            // Save the signature file to disk using the Storage facade
            $singaturePath = storage_path('app/public/signature');

            $criminalHistory->signature = $signatureName;
            $criminalHistory->applicant_id = $userID;

            ModelsCriminalHistorySearch::firstOrCreate(
                ['applicant_id' => $userID],
                [
                    'signature' => $signatureName,
                    'applicant_id' => $userID
                ]
            );

            DB::commit();

            if (!File::exists($singaturePath)) {
                File::makeDirectory($singaturePath, 0755, true);
            }

            Storage::disk('public')->put('signature/' . $signatureName, $signatureBinary);

            // refresh page when form is submitted
            return response()->json(['message' => 'Criminal History Search Consent Form Signed Successfully!'], 200);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }
}
