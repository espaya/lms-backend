<?php

namespace App\Http\Controllers;

use App\Models\EmployeeReferenceCheck;
use App\Models\Profile;
use App\Services\UserProfileService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class EmployeeReferenceController extends Controller
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

            //query employee_reference_check table
            $empRefCheck = EmployeeReferenceCheck::where('applicant_id', $userID)->first();

            return response()->json([
                'empRefCheck' => $empRefCheck,
                'profileData' => $profileData->full_name
            ], 200);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json(['message' => 'An unexpected error occurred']);
        }
    }

    public function store(Request $request)
    {
        $userID = Auth::id();

        $request->validate([
            'company_contacted' => ['required', 'string'],
            'employer_name' => ['required', 'string'],
            'from_date' => ['required'],
            'to_date' => ['required'],
            'eligible_for_hire' => ['required'],
            'comments' => ['required', 'string'],
            'received_by' => ['required'],
            'name_of_company' => ['required', 'string'],
            'signature' => ['required'],
            'rep_signature' => ['required'],
            'rep_title' => ['required', 'string'],
            'company_signature' => ['required_if:received_by,Fax'], // required if received_by == 'Fax'
        ], [
            'company_contacted.required' => 'This field is required',
            'company_contacted.string' => 'Invalid input',

            'employer_name.required' => 'This field is required',
            'employer_name.string' => 'Invalid input',

            'from_date.required' => 'This field is required',
            'to_date.required' => 'This field is required',
            'eligible_for_hire.required' => 'This field is required',
            'comments.required' => 'This field is required',
            'comments.string' => 'Invalid input',
            'received_by.required' => 'This field is required',
            'name_of_company.required' => 'This field is required',
            'name_of_company.string' => 'Invalid',
            'signature.required' => 'This field is required',
            'rep_signature.required' => 'This field is required',
            'rep_title.required' => 'This field is required',
            'rep_title.string' => 'Invalid input',
            'company_signature.required' => 'This field is required',
        ]);

        DB::beginTransaction();

        try {
            $empRef = new EmployeeReferenceCheck();

            // Process applicant signature (always required)
            $signatureData = $request->input('signature');
            $signatureParts = explode(',', $signatureData);
            $signatureEncoded = $signatureParts[1] ?? '';
            $signatureBinary = base64_decode($signatureEncoded);
            $signatureName = time() . '.png';

            // Process representative signature (always required)
            $repSignatureData = $request->input('rep_signature');
            $repSignatureParts = explode(',', $repSignatureData);
            $repSignatureEncoded = $repSignatureParts[1] ?? '';
            $repSignatureBinary = base64_decode($repSignatureEncoded);
            $repSignatureName = time() . '_rep.png';

            // Process company signature (conditional)
            $companySignatureName = null;
            $companySignatureBinary = null;

            if ($request->filled('company_signature')) {
                $companySignatureData = $request->input('company_signature');
                $companySignatureParts = explode(',', $companySignatureData);

                // Check if the signature data is valid (has at least 2 parts)
                if (count($companySignatureParts) >= 2) {
                    $companySignatureEncoded = $companySignatureParts[1];
                    $companySignatureBinary = base64_decode($companySignatureEncoded);
                    $companySignatureName = time() . '_company.png';
                }
            }

            // Prepare data for creation
            $empRefData = [
                'applicant_id' => $userID,
                'company_contacted' => $request->company_contacted,
                'employer_name' => $request->employer_name,
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
                'eligible_for_hire' => $request->eligible_for_hire,
                'comments' => $request->comments,
                'received_by' => $request->received_by,
                'name_of_company' => $request->name_of_company,
                'signature' => $signatureName,
                'rep_signature' => $repSignatureName,
                'rep_title' => $request->rep_title,
                'company_signature' => $companySignatureName,
            ];

            // Create or update the record
            EmployeeReferenceCheck::firstOrCreate(
                ['applicant_id' => $userID],
                $empRefData
            );

            // Create directory if it doesn't exist
            $signaturePath = storage_path('app/public/signature');
            if (!File::exists($signaturePath)) {
                File::makeDirectory($signaturePath, 0755, true);
            }

            DB::commit();

            // Save signature files
            Storage::disk('public')->put('signature/' . $signatureName, $signatureBinary);
            Storage::disk('public')->put('signature/' . $repSignatureName, $repSignatureBinary);

            // Only save company signature if it exists
            if ($companySignatureBinary && $companySignatureName) {
                Storage::disk('public')->put('signature/' . $companySignatureName, $companySignatureBinary);
            }

            return response()->json(['message' => 'Employee Reference Check Signed Successfully']);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . ' on line ' . $ex->getLine());
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    } 
}
