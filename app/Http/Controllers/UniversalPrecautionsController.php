<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\UniversalPrecautions;
use App\Services\UserProfileService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class UniversalPrecautionsController extends Controller
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

            $precautions = UniversalPrecautions::where('applicant_id', $userID)->first();

            return response()->json([
                'precautionsData' => $precautions,
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
            'signature' => 'required'
        ], [
            'signature.required' => 'This field is required',
        ]);

        DB::beginTransaction();

        try {
            $universal = new UniversalPrecautions();

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

            $universal->signature = $signatureName;
            $universal->applicant_id = $userID;

            UniversalPrecautions::firstOrCreate(
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

            return response()->json(['message' => 'Universal Precautions Training Document Signed Successfully'], 200);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());
            return response()->json(['messsage' => 'An unexpected error occurred'], 500);
        }
    }
}
