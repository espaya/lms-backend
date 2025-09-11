<?php

namespace App\Http\Controllers;

use App\Models\EmployeeReferenceCheck;
use App\Services\UserProfileService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

            // query users_profile table
            $user_profile = DB::table('users_profile')->where('applicant_id', $userID)->get();

            //query employee_reference_check table
            $empRefCheck = DB::table('employee_reference_check')->where('applicant_id', $userID)->get();

            return response()->json([
                'userProfile' => $user_profile,
                'empRefChec' => $empRefCheck,
                'profileData' => $profileData
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

            // get signature input
            $signatureData = $request->input('signature');
            $repSignatureData = $request->input('rep_signature');
            $companySignatureData = $request->input('company_signature');

            $signatureParts = explode(',', $signatureData);
            $repSignatureParts = explode(',', $repSignatureData);
            $companySignatureParts = explode(',', $companySignatureData);

            $signatureEncoded = $signatureParts[1]; // Extract the base64-encoded part
            $repSignatureEncoded = $repSignatureParts[1]; // Extract the base64-encoded part
            $companySignatureEncoded = $companySignatureParts[1]; // extract the base64-encoded part

            $signatureBinary = base64_decode($signatureEncoded);
            $repSignatureBinary = base64_decode($repSignatureEncoded);
            $companySignatureBinary = base64_decode($companySignatureEncoded);

            // Generate a unique filename for the signature file
            $signatureName = time() . '.png';
            $repSignatureName = time() . '_rep.png';
            $companySignatureName = time() . '_company.png';

            // Save the signature file
            Storage::put('public/signature/' . $signatureName, $signatureBinary);

            // Save the repSignature file
            Storage::put('public/signature/' . $repSignatureName, $repSignatureBinary);

            // save company contacted signature file
            Storage::put('public/signature/' . $companySignatureName, $companySignatureBinary);

            $empRef->applicant_id = $userID;
            $empRef->company_contacted = $request->company_contacted;
            $empRef->employer_name = $request->employer_name;
            $empRef->from_date = $request->from_date;
            $empRef->to_date = $request->to_date;
            $empRef->eligible_for_hire = $request->eligible_for_hire;
            $empRef->comments = $request->comments;
            $empRef->received_by = $request->received_by;
            $empRef->name_of_company = $request->name_of_company;
            $empRef->signature = $signatureName;
            $empRef->rep_signature = $repSignatureName;
            $empRef->company_signature = $companySignatureName;
            $empRef->rep_title = $request->rep_title;

            EmployeeReferenceCheck::firstOrCreate(
                ['applicant_id' => $userID],
                [
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
                    'company_signature' => $companySignatureName,
                    'rep_title' => $request->rep_title
                ]
            );

            DB::commit();

            return response()->json(['success' => 'Employee Reference Check Signed Successfully']);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }
}
