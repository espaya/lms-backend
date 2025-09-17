<?php

namespace App\Http\Controllers;

use App\Models\EmploymentApplication;
use App\Models\SwornDisclosure;
use App\Services\UserProfileService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class SwornDisclosureController extends Controller
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
            $postion = EmploymentApplication::where('applicant_id', $userID)->first();
            $sworn = SwornDisclosure::where('applicant_id', $userID)->first();

            return response()->json([
                'position' => $postion->position,
                'sworn' => $sworn,
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
            'mailing_address' => 'required',
            'convicted_outside_commonwealth' => 'required',
            'outside_commonwealth_specify' => 'nullable',
            'convicted_pending' => 'required',
            'convicted_pending_specify' => 'nullable',
            'child_abuse' => 'required',
            'wit_signature' => 'required',
            'signature' => 'required',
        ], [
            'mailing_address.required' => 'This field is required',
            'convicted_outside_commonwealth.required' => 'This field is required',
            'convicted_pending.required' => 'This field is required',
            'child_abuse.required' => 'This field is required',
            'wit_signature.required' => 'This field is required',
            'signature.required' => 'This field is required',
        ]);

        DB::beginTransaction();

        try {
            $swornDisclosure = new SwornDisclosure();

            // get signature input
            $signatureData = $request->input('signature');
            $witSignatureData = $request->input('wit_signature');

            $signatureParts = explode(',', $signatureData);
            $signatureEncoded = $signatureParts[1]; // Extract the base64-encoded part
            $witSignatureParts = explode(',', $witSignatureData);
            $witSignatureEncoded = $witSignatureParts[1];

            $signatureBinary = base64_decode($signatureEncoded);
            $witSignatureBinary = base64_decode($witSignatureEncoded);

            // Generate a unique filename for the signature file
            $signatureName = time() . '.png';
            $witSignatureName = time() . '_.png';

            // Save the signature file to disk using the Storage facade
            $singaturePath = storage_path('app/public/signature');


            $swornDisclosure->applicant_id = $userID;
            $swornDisclosure->mailing_address = $request->mailing_address;
            $swornDisclosure->convicted_outside_commonwealth = $request->convicted_outside_commonwealth;
            $swornDisclosure->outside_commonwealth_specify = $request->outside_commonwealth_specify;
            $swornDisclosure->convicted_pending = $request->convicted_pending;
            $swornDisclosure->convicted_pending_specify = $request->convicted_pending_specify;
            $swornDisclosure->child_abuse = $request->child_abuse;
            $swornDisclosure->signature = $signatureName;
            $swornDisclosure->wit_signature = $witSignatureName;

            SwornDisclosure::firstOrCreate(
                ['applicant_id' => $userID],
                [
                    'mailing_address' => $request->mailing_address,
                    'convicted_outside_commonwealth' => $request->convicted_outside_commonwealth,
                    'outside_commonwealth_specify' => $request->outside_commonwealth_specify,
                    'convicted_pending' => $request->convicted_pending,
                    'convicted_pending_specify' => $request->convicted_pending_specify,
                    'child_abuse' => $request->child_abuse,
                    'signature' => $signatureName,
                    'wit_signature' => $witSignatureName,
                    'applicant_id' => $userID
                ]
            );

            DB::commit();

            if (!File::exists($singaturePath)) {
                File::makeDirectory($singaturePath, 0755, true);
            }

            Storage::disk('public')->put('signature/' . $signatureName, $signatureBinary);
            Storage::disk('public')->put('signature/' . $witSignatureName, $witSignatureBinary);

            return response()->json(['message' => 'Sworn Disclosure Statement Signed Successfully']);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());
            return response()->json(['message' => 'An unexpected error occurred']);
        }
    }
}
