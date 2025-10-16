<?php

namespace App\Http\Controllers;

use App\Models\DisclaimerWaiverLiability;
use App\Services\UserProfileService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class DisclaimerWaiverLiabilityController extends Controller
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
            $disclaimer = DB::table('disclaimer_waiver_liability')->where('applicant_id', $userID)->first();

            return response()->json([
                'disclaimer' => $disclaimer,
                'profileData' => $profileData->full_name ?? 'N/A',
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
        ]);

        DB::beginTransaction();

        try {
            $disclaimer = new DisclaimerWaiverLiability();

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

            $disclaimer->signature = $signatureName;
            $disclaimer->applicant_id = $userID;

            DisclaimerWaiverLiability::firstOrCreate(
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
            return response()->json(['message' => 'Disclaimer And Waiver of Liability Signed Successfully!']);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }
}
