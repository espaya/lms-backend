<?php

namespace App\Http\Controllers;

use App\Models\NonCompeteAgreement;
use App\Services\UserProfileService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class NonCompeteAgreementController extends Controller
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

            $non_compete = NonCompeteAgreement::where('applicant_id', $userID)->get();

            return response()->json([
                'non_compete' => $non_compete,
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
            'signature.required' => 'This field is required',
        ]);

        DB::beginTransaction();

        try {
            $compete = new NonCompeteAgreement();

            // get signature input
            $signatureData = $request->input('signature');

            $signatureParts = explode(',', $signatureData);
            $signatureEncoded = $signatureParts[1]; // Extract the base64-encoded part

            $signatureBinary = base64_decode($signatureEncoded);

            // Generate a unique filename for the signature file
            $signatureName = time() . '.png';

            // Save the signature file to disk using the Storage facade
            Storage::put('public/signature/' . $signatureName, $signatureBinary);

            $compete->signature = $signatureName;
            $compete->applicant_id = $userID;

            NonCompeteAgreement::firstOrCreate(
                ['applicant_id' => $userID],
                [
                    'signature' => $signatureName,
                    'applicant_id' => $userID
                ]
            );

            DB::commit();

            return response()->json(['success' => 'Non-Compete Agreement Signed Successfully'], 200);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }
}
