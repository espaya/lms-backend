<?php

namespace App\Http\Controllers;

use App\Models\Verification;
use App\Services\UserProfileService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class VerificationController extends Controller
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

            $verification = Verification::where('applicant_id', $userID)->first();

            return response()->json([
                'verificationData' => $verification,
                'profileData' => $profileData->full_name ?? "N/A"
            ], 200);
        } catch (Exception $ex) {
            Log::error($ex->getMessage() . 'on line: ' . $ex->getLine());
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }

    public function store(Request $request)
    {
        $userID = Auth::id();

        $request->validate([
            'disciplines' => 'required_without_all:checkboxRN, checkboxLPN,checkboxHHA,checkboxCNA',
            'licenseNumber' => 'required',
            'expirationDate' => 'required',
            'dateVerified' => 'required',
            'licenseVerifiedBy' => 'required',
            'actionOutstanding' => 'required',
            'comments' => 'required',
            'signature' => 'required'
        ], [
            'disciplines.required_without_all' => 'Check at least on discipline',
            'licenseNumber.required' => 'This field is required',
            'expirationDate.required' => 'This field is required',
            'dateVerified.required' => 'This field is required',
            'licenseVerifiedBy.required' => 'This field is required',
            'actionOutstanding.required' => 'This field is required',
            'comments.required' => 'This field is required',
            'signature.required' => 'This field is required',
        ]);

        DB::beginTransaction();

        try {
            $verification = new Verification();

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

            $verification->signature = $signatureName;
            $verification->applicant_id = $userID;
            $verification->disciplines = $request->disciplines;
            $verification->licenseNumber = $request->licenseNumber;
            $verification->expirationDate = $request->expirationDate;
            $verification->dateVerified = $request->dateVerified;
            $verification->licenseVerifiedBy = $request->licenseVerifiedBy;
            $verification->actionOutstanding = $request->actionOutstanding;
            $verification->comments = $request->comments;

            Verification::firstOrCreate(
                ['applicant_id' => $userID],
                [
                    'applicant_id' => $userID,
                    'signature' => $signatureName,
                    'disciplines' => implode(',', $request->input('disciplines', [])),
                    'licenseNumber' => $request->licenseNumber,
                    'expirationDate' => $request->expirationDate,
                    'dateVerified' => $request->dateVerified,
                    'licenseVerifiedBy' => $request->licenseVerifiedBy,
                    'actionOutstanding' => $request->actionOutstanding,
                    'comments' => $request->comments
                ]
            );

            DB::commit();

            if (!File::exists($singaturePath)) {
                File::makeDirectory($singaturePath, 0755, true);
            }

            Storage::disk('public')->put('signature/' . $signatureName, $signatureBinary);

            return response()->json(['message' => 'Verification of Professional License']);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }
}
