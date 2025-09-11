<?php

namespace App\Http\Controllers;

use App\Models\HealthSafetyAgreement;
use App\Services\UserProfileService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HealthSafetyController extends Controller
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

            $health = HealthSafetyAgreement::where('applicant_id', $userID)->get();

            return response()->json([
                'healthData' => $health,
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
        ]);

        $health = new HealthSafetyAgreement();

        // get signature input
        $signatureData = $request->input('signature');

        $signatureParts = explode(',', $signatureData);
        $signatureEncoded = $signatureParts[1]; // Extract the base64-encoded part

        $signatureBinary = base64_decode($signatureEncoded);

        // Generate a unique filename for the signature file
        $signatureName = time() . '.png';

        // Save the signature file to disk using the Storage facade
        Storage::put('public/signature/' . $signatureName, $signatureBinary);

        $health->signature = $signatureName;
        $health->applicant_id = $userID;

        HealthSafetyAgreement::firstOrCreate(
            ['applicant_id' => $userID],
            [
                'signature' => $signatureName,
                'applicant_id' => $userID
            ]
        );

        return redirect()->back()->with('success', 'Health Safety Agreement Signed Successfully');
    }
}
