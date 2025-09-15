<?php

namespace App\Http\Controllers;

use App\Models\EmployeeAgreement as ModelsEmployeeAgreement;
use App\Services\UserProfileService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class EmployeeAgreement extends Controller
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

            $agree = ModelsEmployeeAgreement::where('applicant_id', $userID)->first();

            return response()->json([
                'agree' => $agree,
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
            'monday_hour' => 'required',
            'tuesday_hour' => 'required',
            'wednesday_hour' => 'required',
            'thursday_hour' => 'required',
            'friday_hour' => 'required',
            'saturday_hour' => 'required',
            'sunday_hour' => 'required',
            'time_off' => 'required',
            'pay_per_hour' => 'required',
            'other_agreements' => 'required',
            'signature' => 'required'
        ], [
            'monday_hour.required' => 'This field is required',
            'tuesday_hour.required' => 'This field is required',
            'wednesday_hour.required' => 'This field is required',
            'thursday_hour.required' => 'This field is required',
            'friday_hour.required' => 'This field is required',
            'saturday_hour.required' => 'This field is required',
            'sunday_hour.required' => 'This field is required',
            'time_off.required' => 'This field is required',
            'pay_per_hour.required' => 'This field is required',
            'other_agreements.required' => 'This field is required',
            'signature.required' => 'This field is required'
        ]);

        DB::beginTransaction();

        try {
            $empAgree = new ModelsEmployeeAgreement();

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

            $empAgree->signature = $signatureName;
            $empAgree->applicant_id = $userID;
            $empAgree->monday_hour = $request->monday_hour;
            $empAgree->tuesday_hour = $request->tuesday_hour;
            $empAgree->wednesday_hour = $request->wednesday_hour;
            $empAgree->thursday_hour = $request->thursday_hour;
            $empAgree->friday_hour = $request->friday_hour;
            $empAgree->saturday_hour = $request->saturday_hour;
            $empAgree->sunday_hour = $request->sunday_hour;
            $empAgree->time_off = $request->time_off;
            $empAgree->pay_per_hour = $request->pay_per_hour;
            $empAgree->other_agreements = $request->other_agreements;

            ModelsEmployeeAgreement::firstOrCreate(
                ['applicant_id' => $userID],
                [
                    'monday_hour' => $request->monday_hour,
                    'tuesday_hour' => $request->tuesday_hour,
                    'wednesday_hour' => $request->wednesday_hour,
                    'thursday_hour' => $request->thursday_hour,
                    'friday_hour' => $request->friday_hour,
                    'saturday_hour' => $request->saturday_hour,
                    'sunday_hour' => $request->sunday_hour,
                    'time_off' => $request->time_off,
                    'pay_per_hour' => $request->pay_per_hour,
                    'other_agreements' => $request->other_agreements,
                    'signature' => $signatureName,
                    'applicant_id' => $userID
                ]
            );

            DB::commit();

            if (!File::exists($singaturePath)) {
                File::makeDirectory($singaturePath, 0755, true);
            }

            Storage::disk('public')->put('signature/' . $signatureName, $signatureBinary);

            return response()->json(['message' => 'Employee Agreement Signed Successfully']);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }
}
