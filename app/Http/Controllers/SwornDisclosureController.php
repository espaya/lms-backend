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
                'profileData' => $profileData->full_name ?? "N/A"
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
            'mailing_address.required' => 'This field is required.',
            'convicted_outside_commonwealth.required' => 'This field is required.',
            'convicted_pending.required' => 'This field is required.',
            'child_abuse.required' => 'This field is required.',
            'wit_signature.required' => 'Witness signature is required.',
            'signature.required' => 'Applicant signature is required.',
        ]);


        DB::beginTransaction();

        try {

            // decode signatures
            $signatureBinary = base64_decode(explode(',', $request->signature)[1]);
            $witSignatureBinary = base64_decode(explode(',', $request->wit_signature)[1]);

            $signatureName = time() . '.png';
            $witSignatureName = time() . '_wit.png';

            $path = storage_path('app/public/signature');
            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
            }

            // Save/Update database
            $swornDisclosure = SwornDisclosure::updateOrCreate(
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
                ]
            );

            DB::commit();

            // Save files
            Storage::disk('public')->put('signature/' . $signatureName, $signatureBinary);
            Storage::disk('public')->put('signature/' . $witSignatureName, $witSignatureBinary);

            return response()->json(['message' => 'Sworn Disclosure Statement Signed Successfully']);
        } catch (Exception $ex) {

            DB::rollBack();
            Log::error($ex->getMessage());

            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }
}
