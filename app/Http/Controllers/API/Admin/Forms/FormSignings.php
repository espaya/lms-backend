<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\services\UpdateService;
use App\Models\AgencyManagementNotes;
use App\Models\RepSignature;
use App\Services\UserProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FormSignings extends Controller
{
    protected $userProfileService, $updateService;

    public function __construct(UserProfileService $userProfileService, UpdateService $updateService)
    {
        $this->userProfileService = $userProfileService;
        $this->updateService = $updateService;
    }

    public function getProfile($applicant_id)
    {
        $profile = DB::table('users_profile')->where('applicant_id', $applicant_id)->first();
        return $profile;
    }

    public function saveAgencyManagementNotes(Request $request, $applicant_id, $id)
    {
        // validate field
        $request->validate([
            'agency_managements_notes' => ['required', 'string'],
        ]);

        // agency management notes object
        $notes = new AgencyManagementNotes();
        $notes->agency_managements_notes = $request->agency_managements_notes;
        $notes->applicant_id = $applicant_id;
        $notes->application_form_id = $id;

        // save to db
        $notes->save();

        // return back with success message
        return redirect()->back()->with('success', 'Agency Management Notes Added Successfully');
    }

    public function editAgencyManagementNotes($applicant_id, $id)
    {
        return view('admin.applicants.forms.employments-application');
    }

    public function updateAgencyManagementNotes(Request $request, $applicant_id, $id)
    {
        // valiidate field
        $request->validate([
            'agency_management_notes' => ['required', 'strng'],
        ]);

        // agency management notes object
        $notes = new AgencyManagementNotes();
        $notes->agency_managements_notes = $request->agency_managements_notes;
        $notes->applicant_id = $applicant_id;
        $notes->application_form_id = $id;

        // update db
        $notes->update();

        // return with success message
        return redirect()->back()->with('success', 'Agency Management Notes Updated Successfully');
    }


    public function saveDiscalimerWaiverAgencyRepSignature(Request $request, $applicant_id, $id)
    {
        // Validate the request
        $request->validate([
            'rep_signature' => ['required'], // Check the image validation rule
        ]);

        $tableName = 'disclaimer_waiver_liability';

        // Check if a file is provided in the request
        if ($request->input('rep_signature')) {

            $file = $request->input('rep_signature');

            $signatureParts = explode(',', $file);

            $signatureEncoded = $signatureParts[1]; // Extract the base-64 encoded part

            $signatureBinary = base64_decode($signatureEncoded);

            // Generate a unique filename for the uploaded file
            $fileName = time() . '.png';

            // Store the file in the 'public/signature' directory
            Storage::put('public/signature/' . $fileName, $signatureBinary);

            // Conditions for updating the record
            $conditions = [
                'applicant_id' => $applicant_id,
                'id' => $id,
            ];

            // Fields to be updated
            $newValues = [
                'rep_signature' => $fileName,
                'rep_signed_at' => $request->date_rep_signed,
            ];

            // Use Eloquent to update the database record
            $this->updateService->updateRecord($tableName, $conditions, $newValues);

            return redirect()->back()->with('success', 'Agency Representative Signature Added Successfully!');
        }

        // Handle the case when no file is provided
        return redirect()->back()->with('error', 'No signature file provided.');
    }



    public function viewDisclaimerWaiverLiability($applicant_id, $id)
    {
        // page title
        $title = 'Disclaimer and Waiver of Liability | 1staccess Home Care';

        // admin info
        $adminData =  $this->userProfileService->getUser();

        // applicant profile info
        $profileData = $this->getProfile($applicant_id);

        return view('admin.applicants.forms.disclaimer-waiver-liability', 
        [
            'title' => $title, 
            'adminData' => $adminData, 
            'profileData' => $profileData, 
            'applicant_id' => $applicant_id, 
            'id' => $id
        ]);
    }

    public function saveCellularPhoneUseRepSignature(Request $request, $applicant_id, $id)
    {
        // Validate the request
        $request->validate([
            'rep_signature' => ['required'], // Check the image validation rule
        ]);

        $tableName = 'employee_safety';

        // Check if a file is provided in the request
        if ($request->input('rep_signature')) {

            $file = $request->input('rep_signature');

            $signatureParts = explode(',', $file);

            $signatureEncoded = $signatureParts[1]; // Extract the base-64 encoded part

            $signatureBinary = base64_decode($signatureEncoded);

            // Generate a unique filename for the uploaded file
            $fileName = time() . '.png';

            // Store the file in the 'public/signature' directory
            Storage::put('public/signature/' . $fileName, $signatureBinary);

            // Conditions for updating the record
            $conditions = [
                'applicant_id' => $applicant_id,
                'id' => $id,
            ];

            // Fields to be updated
            $newValues = [
                'rep_signature' => $fileName,
            ];

            // Use Eloquent to update the database record
            $this->updateService->updateRecord($tableName, $conditions, $newValues);

            return redirect()->back()->with('success', 'Agency Representative Signature Added Successfully!');
        }

        // Handle the case when no file is provided
        return redirect()->back()->with('error', 'No signature file provided.');
    }


    public function viewCellularPhoneUseRepSignature($applicant_id, $id)
    {
        // page title
        $title = 'Disclaimer and Waiver of Liability | 1staccess Home Care';

        // admin info
        $adminData =  $this->userProfileService->getUser();

        // applicant profile info
        $profileData = $this->getProfile($applicant_id);

        return view('admin.applicants.forms.employee-safety', [
            'title' => $title, 
            'adminData' => $adminData, 
            'profileData' => $profileData, 
            'applicant_id' => $applicant_id, 
            'id' => $id
        ]);

    }

    public function saveEmployeeAgreementEmployerSignature(Request $request, $applicant_id, $id)
    {
        // Validate the request
        $request->validate([
            'employer_signature' => ['required'], // Check the image validation rule
        ]);

        $tableName = 'employee_agreement';

        // Check if a file is provided in the request
        if ($request->input('employer_signature')) {

            $file = $request->input('employer_signature');

            $signatureParts = explode(',', $file);

            $signatureEncoded = $signatureParts[1]; // Extract the base-64 encoded part

            $signatureBinary = base64_decode($signatureEncoded);

            // Generate a unique filename for the uploaded file
            $fileName = time() . '.png';

            // Store the file in the 'public/signature' directory
            Storage::put('public/signature/' . $fileName, $signatureBinary);

            // Conditions for updating the record
            $conditions = [
                'applicant_id' => $applicant_id,
                'id' => $id,
            ];

            // Fields to be updated
            $newValues = [
                'employer_signature' => $fileName,
            ];

            // Use Eloquent to update the database record
            $this->updateService->updateRecord($tableName, $conditions, $newValues);

            return redirect()->back()->with('success', 'Agency Representative Signature Added Successfully!');
        }

        // Handle the case when no file is provided
        return redirect()->back()->with('error', 'No signature file provided.');
    }


    public function saveEmployeeConductHrSignatures(Request $request, $applicant_id, $id)
    {
        // Validate the request
        $request->validate([
            'hr_signature' => ['required'], // Check the image validation rule
        ]);

        $tableName = 'employee_conduct';

        // Check if a file is provided in the request
        if ($request->input('hr_signature')) {

            $file = $request->input('hr_signature');

            $signatureParts = explode(',', $file);

            $signatureEncoded = $signatureParts[1]; // Extract the base-64 encoded part

            $signatureBinary = base64_decode($signatureEncoded);

            // Generate a unique filename for the uploaded file
            $fileName = time() . '.png';

            // Store the file in the 'public/signature' directory
            Storage::put('public/signature/' . $fileName, $signatureBinary);

            // Conditions for updating the record
            $conditions = [
                'applicant_id' => $applicant_id,
                'id' => $id,
            ];

            // Fields to be updated
            $newValues = [
                'hr_signature' => $fileName,
            ];

            // Use Eloquent to update the database record
            $this->updateService->updateRecord($tableName, $conditions, $newValues);

            return redirect()->back()->with('success', 'HR\'s Signature Added Successfully!');
        }

        // Handle the case when no file is provided
        return redirect()->back()->with('error', 'No signature file provided.');
    }

    public function saveEmployeeOrientationRNSignatures(Request $request, $applicant_id, $id)
    {
             // Validate the request
             $request->validate([
                'rn_supervisor_signature' => ['required'], // Check the image validation rule
            ]);
    
            $tableName = 'employee_orientation';
    
            // Check if a file is provided in the request
            if ($request->input('rn_supervisor_signature')) {
    
                $file = $request->input('rn_supervisor_signature');
    
                $signatureParts = explode(',', $file);
    
                $signatureEncoded = $signatureParts[1]; // Extract the base-64 encoded part
    
                $signatureBinary = base64_decode($signatureEncoded);
    
                // Generate a unique filename for the uploaded file
                $fileName = time() . '.png';
    
                // Store the file in the 'public/signature' directory
                Storage::put('public/signature/' . $fileName, $signatureBinary);
    
                // Conditions for updating the record
                $conditions = [
                    'applicant_id' => $applicant_id,
                    'id' => $id,
                ];
    
                // Fields to be updated
                $newValues = [
                    'rn_supervisor_signature' => $fileName,
                ];
    
                // Use Eloquent to update the database record
                $this->updateService->updateRecord($tableName, $conditions, $newValues);
    
                return redirect()->back()->with('success', 'RN Supervisor\'s Signature Added Successfully!');
            }
    
            // Handle the case when no file is provided
            return redirect()->back()->with('error', 'No signature file provided.');
    }

    public function saveEmployeeOrientationHrSignatures(Request $request, $applicant_id, $id)
    {
         // Validate the request
         $request->validate([
            'hr_supervisor_signature' => ['required'], // Check the image validation rule
        ]);

        $tableName = 'employee_orientation';

        // Check if a file is provided in the request
        if ($request->input('hr_supervisor_signature')) {

            $file = $request->input('hr_supervisor_signature');

            $signatureParts = explode(',', $file);

            $signatureEncoded = $signatureParts[1]; // Extract the base-64 encoded part

            $signatureBinary = base64_decode($signatureEncoded);

            // Generate a unique filename for the uploaded file
            $fileName = time() . '.png';

            // Store the file in the 'public/signature' directory
            Storage::put('public/signature/' . $fileName, $signatureBinary);

            // Conditions for updating the record
            $conditions = [
                'applicant_id' => $applicant_id,
                'id' => $id,
            ];

            // Fields to be updated
            $newValues = [
                'hr_signature' => $fileName,
            ];

            // Use Eloquent to update the database record
            $this->updateService->updateRecord($tableName, $conditions, $newValues);

            return redirect()->back()->with('success', 'HR\'s Signature Added Successfully!');
        }

        // Handle the case when no file is provided
        return redirect()->back()->with('error', 'No signature file provided.');
    }


    public function saveHRAttendanceTardinessSignature(Request $request, $applicant_id, $id)
    {
        // Validate the request
        $request->validate([
            'hr_signature' => ['required'], // Check the image validation rule
        ]);

        $tableName = 'attendance_tardiness';

        // Check if a file is provided in the request
        if ($request->input('hr_signature')) {

            $file = $request->input('hr_signature');

            $signatureParts = explode(',', $file);

            $signatureEncoded = $signatureParts[1]; // Extract the base-64 encoded part

            $signatureBinary = base64_decode($signatureEncoded);

            // Generate a unique filename for the uploaded file
            $fileName = time() . '.png';

            // Store the file in the 'public/signature' directory
            Storage::put('public/signature/' . $fileName, $signatureBinary);

            // Conditions for updating the record
            $conditions = [
                'applicant_id' => $applicant_id,
                'id' => $id,
            ];

            // Fields to be updated
            $newValues = [
                'hr_signature' => $fileName,
            ];

            // Use Eloquent to update the database record
            $this->updateService->updateRecord($tableName, $conditions, $newValues);

            return redirect()->back()->with('success', 'HR\'s Signature Added Successfully!');
        }

        // Handle the case when no file is provided
        return redirect()->back()->with('error', 'No signature file provided.');   
    }


    public function saveInfectionControlAgreementRepSignature(Request $request, $applicant_id, $id)
    {
         // Validate the request
         $request->validate([
            'agency_rep_signature' => ['required'], // Check the image validation rule
        ]);

        $tableName = 'infection_control_agreement';

        // Check if a file is provided in the request
        if ($request->input('agency_rep_signature')) {

            $file = $request->input('agency_rep_signature');

            $signatureParts = explode(',', $file);

            $signatureEncoded = $signatureParts[1]; // Extract the base-64 encoded part

            $signatureBinary = base64_decode($signatureEncoded);

            // Generate a unique filename for the uploaded file
            $fileName = time() . '.png';

            // Store the file in the 'public/signature' directory
            Storage::put('public/signature/' . $fileName, $signatureBinary);

            // Conditions for updating the record
            $conditions = [
                'applicant_id' => $applicant_id,
                'id' => $id,
            ];

            // Fields to be updated
            $newValues = [
                'agency_rep_signature' => $fileName,
            ];

            // Use Eloquent to update the database record
            $this->updateService->updateRecord($tableName, $conditions, $newValues);

            return redirect()->back()->with('success', 'Agency Rep\'s Signature Added Successfully!');
        }

        // Handle the case when no file is provided
        return redirect()->back()->with('error', 'No signature file provided.'); 
    }


    public function saveHealthSafetyAgreement(Request $request, $applicant_id, $id)
    {
         // Validate the request
         $request->validate([
            'agency_rep_signature' => ['required'], // Check the image validation rule
        ]);

        $tableName = 'health_safety_agreement';

        // Check if a file is provided in the request
        if ($request->input('agency_rep_signature')) {

            $file = $request->input('agency_rep_signature');

            $signatureParts = explode(',', $file);

            $signatureEncoded = $signatureParts[1]; // Extract the base-64 encoded part

            $signatureBinary = base64_decode($signatureEncoded);

            // Generate a unique filename for the uploaded file
            $fileName = time() . '.png';

            // Store the file in the 'public/signature' directory
            Storage::put('public/signature/' . $fileName, $signatureBinary);

            // Conditions for updating the record
            $conditions = [
                'applicant_id' => $applicant_id,
                'id' => $id,
            ];

            // Fields to be updated
            $newValues = [
                'agency_rep_signature' => $fileName,
            ];

            // Use Eloquent to update the database record
            $this->updateService->updateRecord($tableName, $conditions, $newValues);

            return redirect()->back()->with('success', 'Agency Rep\'s Signature Added Successfully!');
        }

        // Handle the case when no file is provided
        return redirect()->back()->with('error', 'No signature file provided.'); 
    }

    public function saveNonCompeteAgreementRepSignature(Request $request, $applicant_id, $id)
    {
         // Validate the request
         $request->validate([
            'agency_rep_signature' => ['required'], // Check the image validation rule
        ]);

        $tableName = 'non_compete_agreement';

        // Check if a file is provided in the request
        if ($request->input('agency_rep_signature')) {

            $file = $request->input('agency_rep_signature');

            $signatureParts = explode(',', $file);

            $signatureEncoded = $signatureParts[1]; // Extract the base-64 encoded part

            $signatureBinary = base64_decode($signatureEncoded);

            // Generate a unique filename for the uploaded file
            $fileName = time() . '.png';

            // Store the file in the 'public/signature' directory
            Storage::put('public/signature/' . $fileName, $signatureBinary);

            // Conditions for updating the record
            $conditions = [
                'applicant_id' => $applicant_id,
                'id' => $id,
            ];

            // Fields to be updated
            $newValues = [
                'agency_rep_signature' => $fileName,
            ];

            // Use Eloquent to update the database record
            $this->updateService->updateRecord($tableName, $conditions, $newValues);

            return redirect()->back()->with('success', 'Agency Rep\'s Signature Added Successfully!');
        }

        // Handle the case when no file is provided
        return redirect()->back()->with('error', 'No signature file provided.');
    }

    public function viewHRAttendanceTardinessSignature(Request $request, $applicant_id, $id)
    {
        // Validate the request
        $request->validate([
            'hr_signature' => ['required'], // Check the image validation rule
        ]);

        $tableName = 'attendance_tardiness';

        // Check if a file is provided in the request
        if ($request->input('hr_signature')) {

            $file = $request->input('hr_signature');

            $signatureParts = explode(',', $file);

            $signatureEncoded = $signatureParts[1]; // Extract the base-64 encoded part

            $signatureBinary = base64_decode($signatureEncoded);

            // Generate a unique filename for the uploaded file
            $fileName = time() . '.png';

            // Store the file in the 'public/signature' directory
            Storage::put('public/signature/' . $fileName, $signatureBinary);

            // Conditions for updating the record
            $conditions = [
                'applicant_id' => $applicant_id,
                'id' => $id,
            ];

            // Fields to be updated
            $newValues = [
                'hr_signature' => $fileName,
            ];

            // Use Eloquent to update the database record
            $this->updateService->updateRecord($tableName, $conditions, $newValues);

            return redirect()->back()->with('success', 'HR\'s Signature Added Successfully!');
        }

        // Handle the case when no file is provided
        return redirect()->back()->with('error', 'No signature file provided.');   
    }


    public function saveSupervisorAttendanceTardinessSignature(Request $request, $applicant_id, $id)
    {
        // Validate the request
        $request->validate([
            'supervisor_signature' => ['required'], // Check the image validation rule
        ]);

        $tableName = 'attendance_tardiness';

        // Check if a file is provided in the request
        if ($request->input('supervisor_signature')) {

            $file = $request->input('supervisor_signature');

            $signatureParts = explode(',', $file);

            $signatureEncoded = $signatureParts[1]; // Extract the base-64 encoded part

            $signatureBinary = base64_decode($signatureEncoded);

            // Generate a unique filename for the uploaded file
            $fileName = time() . '.png';

            // Store the file in the 'public/signature' directory
            Storage::put('public/signature/' . $fileName, $signatureBinary);

            // Conditions for updating the record
            $conditions = [
                'applicant_id' => $applicant_id,
                'id' => $id,
            ];

            // Fields to be updated
            $newValues = [
                'supervisor_signature' => $fileName,
            ];

            // Use Eloquent to update the database record
            $this->updateService->updateRecord($tableName, $conditions, $newValues);

            return redirect()->back()->with('success', 'Supervisor\'s Signature Added Successfully!');
        }

        // Handle the case when no file is provided
        return redirect()->back()->with('error', 'No signature file provided.');
    }

    public function viewSupervisorAttendanceTardinessSignature(Request $request, $applicant_id, $id)
    {
        // Validate the request
        $request->validate([
            'supervisor_signature' => ['required'], // Check the image validation rule
        ]);

        $tableName = 'attendance_tardiness';

        // Check if a file is provided in the request
        if ($request->input('supervisor_signature')) {

            $file = $request->input('supervisor_signature');

            $signatureParts = explode(',', $file);

            $signatureEncoded = $signatureParts[1]; // Extract the base-64 encoded part

            $signatureBinary = base64_decode($signatureEncoded);

            // Generate a unique filename for the uploaded file
            $fileName = time() . '.png';

            // Store the file in the 'public/signature' directory
            Storage::put('public/signature/' . $fileName, $signatureBinary);

            // Conditions for updating the record
            $conditions = [
                'applicant_id' => $applicant_id,
                'id' => $id,
            ];

            // Fields to be updated
            $newValues = [
                'supervisor_signature' => $fileName,
            ];

            // Use Eloquent to update the database record
            $this->updateService->updateRecord($tableName, $conditions, $newValues);

            return redirect()->back()->with('success', 'Supervisor\'s Signature Added Successfully!');
        }

        // Handle the case when no file is provided
        return redirect()->back()->with('error', 'No signature file provided.');   
    }


    public function viewEmployeeAgreementEmployerSignature($applicant_id, $id)
    {
        // page title
        $title = 'Disclaimer and Waiver of Liability | 1staccess Home Care';

        // admin info
        $adminData =  $this->userProfileService->getUser();

        // applicant profile info
        $profileData = $this->getProfile($applicant_id);

        return view('admin.applicants.forms.employee-agreement', [
            'title' => $title, 
            'adminData' => $adminData, 
            'profileData' => $profileData, 
            'applicant_id' => $applicant_id, 
            'id' => $id
        ]);
    }

    public function saveEmployeeConductSupervisorSignatures(Request $request, $applicant_id, $id)
    {
        // Validate the request
        $request->validate([
            'supervisor_signature' => ['required'], // Check the image validation rule
        ]);

        $tableName = 'employee_conduct';

        // Check if a file is provided in the request
        if ($request->input('supervisor_signature')) {

            $file = $request->input('supervisor_signature');

            $signatureParts = explode(',', $file);

            $signatureEncoded = $signatureParts[1]; // Extract the base-64 encoded part

            $signatureBinary = base64_decode($signatureEncoded);

            // Generate a unique filename for the uploaded file
            $fileName = time() . '.png';

            // Store the file in the 'public/signature' directory
            Storage::put('public/signature/' . $fileName, $signatureBinary);

            // Conditions for updating the record
            $conditions = [
                'applicant_id' => $applicant_id,
                'id' => $id,
            ];

            // Fields to be updated
            $newValues = [
                'supervisor_signature' => $fileName,
            ];

            // Use Eloquent to update the database record
            $this->updateService->updateRecord($tableName, $conditions, $newValues);

            return redirect()->back()->with('success', 'Supervisor\'s Signature Added Successfully!');
        }

        // Handle the case when no file is provided
        return redirect()->back()->with('error', 'No signature file provided.');
    }

    public function viewEmployeeConductSupervisorSignatures($applicant_id, $id)
    {
        // page title
        $title = 'Employee Notification of Policy Employee Conduct | 1staccess Home Care';

        // admin info
        $adminData =  $this->userProfileService->getUser();

        // applicant profile info
        $profileData = $this->getProfile($applicant_id);

        return view('admin.applicants.forms.employee-conduct', [
            'title' => $title, 
            'adminData' => $adminData, 
            'profileData' => $profileData, 
            'applicant_id' => $applicant_id, 
            'id' => $id
        ]);
    }

    public function viewEmployeeConductHrSignatures($applicant_id, $id)
    {
        // page title
        $title = 'Employee Notification of Policy Employee Conduct | 1staccess Home Care';

        // admin info
        $adminData =  $this->userProfileService->getUser();

        // applicant profile info
        $profileData = $this->getProfile($applicant_id);

        $employee_conduct = DB::table('employee_conduct')->where('applicant_id', $applicant_id)->where('id', $id)->first();

        $employee_conduct_data = [];

        if($employee_conduct)
        {

            $id = $employee_conduct->id;
            $applicant_id = $employee_conduct->applicant_id;
            $signature = $employee_conduct->signature;
            $created_at = $employee_conduct->created_at;

            $employee_conduct_data[] = [
                'id' => $id,
                'applicant_id' => $applicant_id,
                'signature' => $signature,
                'created_at' => $created_at,
            ];
        }

        return view('admin.applicants.forms.employee-conduct', [
            'title' => $title, 
            'adminData' => $adminData, 
            'profileData' => $profileData, 
            'applicant_id' => $applicant_id, 
            'id' => $id,
            'employee_conduct_data' => $employee_conduct_data
        ]);
    }

    public function viewSmokingInWorkplaceSupervisorSignature($applicant_id, $id)
    {
        // page title
        $title = 'Employee Notification of Policy: Smoking In The Workplace | 1staccess Home Care';

        // admin info
        $adminData =  $this->userProfileService->getUser();

        // applicant profile info
        $profileData = $this->getProfile($applicant_id);

        return view('admin.applicants.forms.smoking-in-the-workplace-conduct', [
            'title' => $title, 
            'adminData' => $adminData, 
            'profileData' => $profileData, 
            'applicant_id' => $applicant_id, 
            'id' => $id
        ]);
    }

    public function saveSmokingInWorkplaceSupervisorSignature(Request $request, $applicant_id, $id)
    {
        // Validate the request
        $request->validate([
            'supervisor_signature' => ['required'], // Check the image validation rule
        ]);

        $tableName = 'smoking_in_the_workplace';

        // Check if a file is provided in the request
        if ($request->input('supervisor_signature')) {

            $file = $request->input('supervisor_signature');

            $signatureParts = explode(',', $file);

            $signatureEncoded = $signatureParts[1]; // Extract the base-64 encoded part

            $signatureBinary = base64_decode($signatureEncoded);

            // Generate a unique filename for the uploaded file
            $fileName = time() . '.png';

            // Store the file in the 'public/signature' directory
            Storage::put('public/signature/' . $fileName, $signatureBinary);

            // Conditions for updating the record
            $conditions = [
                'applicant_id' => $applicant_id,
                'id' => $id,
            ];

            // Fields to be updated
            $newValues = [
                'supervisor_signature' => $fileName,
            ];

            // Use Eloquent to update the database record
            $this->updateService->updateRecord($tableName, $conditions, $newValues);

            return redirect()->back()->with('success', 'Supervisor\'s Signature Added Successfully!');
        }

        // Handle the case when no file is provided
        return redirect()->back()->with('error', 'No signature file provided.');
    }

    public function viewSmokingInWorkplaceHRSignature($applicant_id, $id)
    {
        // page title
        $title = 'Employee Notification of Policy: Smoking In The Workplace | 1staccess Home Care';

        // admin info
        $adminData =  $this->userProfileService->getUser();

        // applicant profile info
        $profileData = $this->getProfile($applicant_id);

        return view('admin.applicants.forms.smoking-in-the-workplace', [
            'title' => $title, 
            'adminData' => $adminData, 
            'profileData' => $profileData, 
            'applicant_id' => $applicant_id, 
            'id' => $id
        ]);
    }

    public function saveSmokingInWorkplaceHRSignature(Request $request, $applicant_id, $id)
    {
         // Validate the request
         $request->validate([
            'hr_signature' => ['required'], // Check the image validation rule
        ]);

        $tableName = 'smoking_in_the_workplace';

        // Check if a file is provided in the request
        if ($request->input('hr_signature')) {

            $file = $request->input('hr_signature');

            $signatureParts = explode(',', $file);

            $signatureEncoded = $signatureParts[1]; // Extract the base-64 encoded part

            $signatureBinary = base64_decode($signatureEncoded);

            // Generate a unique filename for the uploaded file
            $fileName = time() . '.png';

            // Store the file in the 'public/signature' directory
            Storage::put('public/signature/' . $fileName, $signatureBinary);

            // Conditions for updating the record
            $conditions = [
                'applicant_id' => $applicant_id,
                'id' => $id,
            ];

            // Fields to be updated
            $newValues = [
                'hr_signature' => $fileName,
            ];

            // Use Eloquent to update the database record
            $this->updateService->updateRecord($tableName, $conditions, $newValues);

            return redirect()->back()->with('success', 'HR\'s Signature Added Successfully!');
        }
    }

    public function viewPolicyRepSignature($applicant_id, $id)
    {
         // page title
         $title = 'Policies and Procedures: Orientation Acknowledgement | 1staccess Home Care';

         // admin info
         $adminData =  $this->userProfileService->getUser();
 
         // applicant profile info
         $profileData = $this->getProfile($applicant_id);
 
         return view('admin.applicants.forms.policies-and-procedures', [
             'title' => $title, 
             'adminData' => $adminData, 
             'profileData' => $profileData, 
             'applicant_id' => $applicant_id, 
             'id' => $id
         ]);
    }

    public function savePolicyRepSignature(Request $request, $applicant_id, $id)
    {
         // Validate the request
         $request->validate([
            'rep_signature' => ['required'], // Check the image validation rule
        ]);

        $tableName = 'policy_and_procedure';

        // Check if a file is provided in the request
        if ($request->input('rep_signature')) {

            $file = $request->input('rep_signature');

            $signatureParts = explode(',', $file);

            $signatureEncoded = $signatureParts[1]; // Extract the base-64 encoded part

            $signatureBinary = base64_decode($signatureEncoded);

            // Generate a unique filename for the uploaded file
            $fileName = time() . '.png';

            // Store the file in the 'public/signature' directory
            Storage::put('public/signature/' . $fileName, $signatureBinary);

            // Conditions for updating the record
            $conditions = [
                'applicant_id' => $applicant_id,
                'id' => $id,
            ];

            // Fields to be updated
            $newValues = [
                'rep_signature' => $fileName,
            ];

            // Use Eloquent to update the database record
            $this->updateService->updateRecord($tableName, $conditions, $newValues);

            return redirect()->back()->with('success', 'Agency Representative\'s Signature Added Successfully!');
        }
    }

    public function viewNonCompeteAgencyRepSignature($applicant_id, $id)
    {
        // page title
        $title = 'Non Compete Agreement | 1staccess Home Care';

        // admin info
        $adminData =  $this->userProfileService->getUser();

        // applicant profile info
        $profileData = $this->getProfile($applicant_id);

        dd($profileData);

        return view('admin.applicants.forms.non-compete-agreement', [
            'title' => $title, 
            'adminData' => $adminData, 
            'profileData' => $profileData, 
            'applicant_id' => $applicant_id, 
            'id' => $id
        ]);
    }

    public function saveNonCompeteAgencyRepSignature(Request $request, $applicant_id, $id)
    {
        // Validate the request
        $request->validate([
            'agency_rep_signature' => ['required'], // Check the image validation rule
        ]);

        $tableName = 'non_compete_agreement';

        // Check if a file is provided in the request
        if ($request->input('agency_rep_signature')) {

            $file = $request->input('agency_rep_signature');

            $signatureParts = explode(',', $file);

            $signatureEncoded = $signatureParts[1]; // Extract the base-64 encoded part

            $signatureBinary = base64_decode($signatureEncoded);

            // Generate a unique filename for the uploaded file
            $fileName = time() . '.png';

            // Store the file in the 'public/signature' directory
            Storage::put('public/signature/' . $fileName, $signatureBinary);

            // Conditions for updating the record
            $conditions = [
                'applicant_id' => $applicant_id,
                'id' => $id,
            ];

            // Fields to be updated
            $newValues = [
                'agency_rep_signature' => $fileName,
            ];

            // Use Eloquent to update the database record
            $this->updateService->updateRecord($tableName, $conditions, $newValues);

            return redirect()->back()->with('success', 'Agency Representative\'s Signature Added Successfully!');
        }
    }
}
