<?php

namespace App\Http\Controllers;

use App\Models\AcademicTrades;
use App\Models\Emergency;
use App\Models\EmploymentApplication;
use App\Models\User;
use App\Models\Language;
use App\Models\PastEmpInfo;
use App\Models\PremanentAddress;
use App\Models\PresentAdrress;
use App\Models\Profile;
use App\Models\Reference;
use App\Models\Signature;
use App\Services\UserProfileService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ApplicationController extends Controller
{
    /*****
     * 
     *  This file is for handling Employment Application Forms 
     * 
     * ****/

    protected $userProfileService;

    /**
     * Create a new controller instance.   Dashboard Controller
     *
     * @return void
     */
    public function __construct(UserProfileService $userProfileService)
    {
        // $this->middleware('auth');
        $this->userProfileService = $userProfileService;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(UserProfileService $userProfileService)
    {
        try {
            $profileData = $userProfileService->getUserProfileData();
            // check if applicant has already filled the application form
            $userID = Auth::id(); // active user ID

            $employmentApplication = User::with(['reference', 'signature', 'applicationForm', 'academic'])->find($userID);

            return response()->json([
                'employmentApplication' => $employmentApplication,
                'profileData' =>  $profileData,
            ], 200);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }

    public function store(Request $request)
    {
        // validate form data
        $request->validate([
            // employments_application table
            'employee_hire_date'                            => 'required',
            'SSN'                                           => 'required|regex:/^\d{3}-\d{2}-\d{4}$/',
            'furnish_work'                                  => 'required',
            'employment_desired'                            => 'nullable',
            'position'                                      => 'required',
            'date_start'                                    => 'required',
            'salary'                                        => 'required',
            'employed_now'                                  => 'required',
            'inqure_present_employer'                       => 'nullable',
            'applied_before'                                => 'required',
            'where'                                         => ['nullable', 'required_if:applied_before,Yes'],
            'when'                                          => ['nullable', 'required_if:applied_before,Yes'],
            'on_layoff_subject_to_recall'                   => 'required',
            'travel_if_required'                            => 'required',
            'relocate_if_required'                          => 'required',
            'overtime_if_required'                          => 'required',
            'attendance_requirements_position'              => 'required',
            'bonded'                                        => 'required',
            'convicted'                                     => 'required',
            'explain_convicted'                             => 'nullable',
            'drivers_license'                               => 'required',
            'drivers_license_state'                         => 'required',
            'special_skills_qualifications'                 => 'required',

            // users_profile table
            'phone'                                         => 'required|regex:/^\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}$/',
            'full_name'                                     => 'required|regex:/^[a-zA-Z., -]/',

            // present address table
            'present_address'                               => 'required|string',
            'present_city'                                  => 'required|string',
            'present_state'                                 => 'required',
            'present_zip'                                   => 'required|regex:/^\d{5}(-\d{4})?$/',

            'present_permanent_address'                     => 'required',

            // permanent address table
            'permanent_address' => ['nullable', 'required_if:present_permanent_address,No', 'string'],
            'permanent_city' => ['nullable', 'required_if:present_permanent_address,No', 'string'],
            'permanent_state' => ['nullable', 'required_if:present_permanent_address,No', 'string'],
            'permanent_zip' => ['nullable', 'required_if:present_permanent_address,No', 'regex:/^\d{5}(-\d{4})?$/'],

            // academic/education/trades
            'edu_current_name_location_school'              => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
            'edu_current_number_years'                      => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
            'edu_current_did_graduate'                      => 'required',
            'edu_current_subjects_studied'                  => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
            'edu_last_name_location_school'                 => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
            'edu_last_number_years'                         => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
            'edu_last_did_graduate'                        => 'required',
            'edu_last_subjects_studied'                     => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
            'trades_current_name_location_school'           => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
            'trades_current_number_years'                   => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
            'trades_current_did_graduate'                   => 'required',
            'trades_current_subjects_studied'               => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
            'trades_last_current_name_location_school'      => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
            'trades_last_current_number_years'              => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
            'trades_last_current_did_graduate'              => 'required',
            'trades_last_subjects_studied'                  => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',

            // past employment info
            'from_date_1'                                   => 'required',
            'to_date_1'                                     => 'required',
            'name_address_employer_1'                       => 'required|regex:/^[a-zA-Z 0-9,. -]+$/',
            'phone_number_1'                                => 'required|regex:/^\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}$/',
            'job_1'                                         => 'required|regex:/^[a-zA-Z]+$/',
            'reason_leaving_1'                              => 'required',
            'salary_1'                                      => 'required',

            'from_date_2'                                   => 'nullable',
            'to_date_2'                                     => 'nullable',
            'name_address_employer_2'                       => 'nullable|string',
            'phone_number_2'                                => 'nullable|regex:/^\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}$/',
            'job_2'                                         => 'nullable|string',
            'reason_leaving_2'                              => 'nullable|string',
            'salary_2'                                      => 'nullable',

            'from_date_3'                                   => 'nullable',
            'to_date_3'                                     => 'nullable',
            'name_address_employer_3'                       => 'nullable|string',
            'phone_number_3'                                => 'nullable||regex:/^\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}$/',
            'job_3'                                         => 'nullable|string',
            'reason_leaving_3'                              => 'nullable|string',
            'salary_3'                                      => 'nullable',

            // reference table
            'reference_name_1'                      => 'required|string',
            'reference_address_1'                   => 'required|string',
            'reference_phone_1'                     => 'required|regex:/^\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}$/',
            'reference_years_acquainted_1'          => 'required|string',
            'reference_name_2'                      => 'required|string',
            'reference_address_2'                   => 'required|string',
            'reference_phone_2'                     => 'required|regex:/^\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}$/',
            'reference_years_acquainted_2'          => 'required|string',
            'reference_name_3'                      => 'required|string',
            'reference_address_3'                   => 'required|string',
            'reference_phone_3'                     => 'required|regex:/^\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}$/',
            'reference_years_acquainted_3'          => 'required|string',

            // language table
            'language_1'                            => 'required|regex:/^[a-z A-Z]+$/',
            'read_write_1'                          => 'required|regex:/^[a-z A-Z]+$/',
            'read_speak_1'                          => 'required|regex:/^[a-z A-Z]+$/',
            'speak_only_1'                          => 'required|regex:/^[a-z A-Z]+$/',

            'language_2'                            => 'nullable|string|regex:/^[a-z A-Z]{3,20}+$/',
            'read_write_2'                          => 'nullable|string|regex:/^[a-z A-Z]{3,10}+$/',
            'read_speak_2'                          => 'nullable|string|regex:/^[a-z A-Z]{3,10}+$/',
            'speak_only_2'                          => 'nullable|string|regex:/^[a-z A-Z]{3,10}+$/',

            // emergency table
            'emergency_address'                     => 'required|string',
            'emergency_city'                        => 'required|string',
            'emergency_state'                       => 'required|string',
            'emergency_zip'                         => 'required',

            // signature table
            'signature'                             => 'required',

        ], [
            'employee_hire_date.required' => 'This field is required',
            'SSN.required' => 'This field is required'
        ]);

        $userID = Auth::id(); // id of authenticated user

        DB::beginTransaction();

        try {
            $EmpApp = new EmploymentApplication;
            $AcadTrad = new AcademicTrades();
            $UserProfile = new Profile();
            $Emergency = new Emergency();
            $PastEmp = new PastEmpInfo();
            $Signature = new Signature();
            $PresAddr = new PresentAdrress();
            $PermAddr = new PremanentAddress();
            $Reference = new Reference();
            $Lang = new Language();

            // language
            $Lang->language_1 = $request->language_1;
            $Lang->read_write_1 = $request->read_write_1;
            $Lang->read_speak_1 = $request->read_speak_1;
            $Lang->speak_only_1 = $request->speak_only_1;

            $Lang->language_2 = $request->language_2;
            $Lang->read_write_2 = $request->read_write_2;
            $Lang->read_speak_2 = $request->read_speak_2;
            $Lang->speak_only_2 = $request->speak_only_2;
            $Lang->applicant_id = $userID;

            // reference
            $Reference->reference_name_1 = $request->reference_name_1;
            $Reference->reference_address_1 = $request->reference_address_1;
            $Reference->reference_phone_1 = $request->reference_phone_1;
            $Reference->reference_years_acquainted_1 = $request->reference_years_acquainted_1;
            $Reference->reference_name_2 = $request->reference_name_2;
            $Reference->reference_address_2 = $request->reference_address_2;
            $Reference->reference_phone_2 = $request->reference_phone_2;
            $Reference->reference_years_acquainted_2 = $request->reference_years_acquainted_2;
            $Reference->reference_name_3 = $request->reference_name_3;
            $Reference->reference_address_3 = $request->reference_address_3;
            $Reference->reference_phone_3 = $request->reference_phone_3;
            $Reference->reference_years_acquainted_3 = $request->reference_years_acquainted_3;
            $Reference->applicant_id = $userID;

            // employment application
            $EmpApp->SSN = $request->SSN;
            $EmpApp->employee_hire_date = $request->employee_hire_date;
            $EmpApp->furnish_work = $request->furnish_work;
            $EmpApp->employment_desired = $request->employment_desired;
            $EmpApp->position = $request->position;
            $EmpApp->date_start = $request->date_start;
            $EmpApp->salary = $request->salary;
            $EmpApp->employed_now = $request->employed_now;
            $EmpApp->inqure_present_employer = $request->inqure_present_employer;
            $EmpApp->applied_before = $request->applied_before;
            $EmpApp->where = $request->where;
            $EmpApp->when = $request->when;
            $EmpApp->on_layoff_subject_to_recall = $request->on_layoff_subject_to_recall;
            $EmpApp->travel_if_required = $request->travel_if_required;
            $EmpApp->relocate_if_required = $request->relocate_if_required;
            $EmpApp->overtime_if_required = $request->overtime_if_required;
            $EmpApp->attendance_requirements_position = $request->attendance_requirements_position;
            $EmpApp->bonded = $request->bonded;
            $EmpApp->convicted = $request->convicted;
            $EmpApp->explain_convicted = $request->explain_convicted;
            $EmpApp->drivers_license = $request->drivers_license;
            $EmpApp->drivers_license_state = $request->drivers_license_state;
            $EmpApp->special_skills_qualifications = $request->special_skills_qualifications;
            $EmpApp->applicant_id = $userID;

            // present address
            $PresAddr->present_address = $request->present_address;
            $PresAddr->present_city = $request->present_city;
            $PresAddr->present_state = $request->present_state;
            $PresAddr->present_zip  = $request->present_zip;
            $PresAddr->applicant_id = $userID;

            if ($request->input('present_permanent_address') == 'Yes') {
                // permanent address
                $PermAddr->permanent_address = $request->present_address;
                $PermAddr->permanent_city = $request->present_city;
                $PermAddr->permanent_state = $request->present_state;
                $PermAddr->permanent_zip = $request->present_zip;
                $PermAddr->applicant_id = $userID;
            } else {
                // permanent address
                $PermAddr->permanent_address = $request->permanent_address;
                $PermAddr->permanent_city = $request->permanent_city;
                $PermAddr->permanent_state = $request->permanent_state;
                $PermAddr->permanent_zip = $request->permanent_zip;
                $PermAddr->applicant_id = $userID;
            }

            // Academic, Education, Trades Business
            $AcadTrad->edu_current_name_location_school = $request->edu_current_name_location_school;
            $AcadTrad->edu_current_number_years = $request->edu_current_number_years;
            $AcadTrad->edu_current_did_graduate = $request->edu_current_did_graduate;
            $AcadTrad->edu_current_subjects_studied = $request->edu_current_subjects_studied;
            $AcadTrad->edu_last_name_location_school = $request->edu_last_name_location_school;
            $AcadTrad->edu_last_number_years = $request->edu_last_number_years;
            $AcadTrad->edu_last_did_graduate = $request->edu_last_did_graduate;
            $AcadTrad->edu_last_subjects_studied = $request->edu_last_subjects_studied;

            $AcadTrad->trades_current_name_location_school = $request->trades_current_name_location_school;
            $AcadTrad->trades_current_number_years = $request->trades_current_number_years;
            $AcadTrad->trades_current_did_graduate = $request->trades_current_did_graduate;
            $AcadTrad->trades_current_subjects_studied = $request->trades_current_subjects_studied;
            $AcadTrad->trades_last_current_name_location_school = $request->trades_last_current_name_location_school;
            $AcadTrad->trades_last_current_number_years = $request->trades_last_current_number_years;
            $AcadTrad->trades_last_current_did_graduate = $request->trades_last_current_did_graduate;
            $AcadTrad->trades_last_subjects_studied = $request->trades_last_subjects_studied;
            $AcadTrad->applicant_id = $userID;

            // past employment info
            $PastEmp->from_date_1 = $request->from_date_1;
            $PastEmp->to_date_1 = $request->to_date_1;
            $PastEmp->name_address_employer_1 = $request->name_address_employer_1;
            $PastEmp->phone_number_1 = $request->phone_number_1;
            $PastEmp->job_1 = $request->job_1;
            $PastEmp->reason_leaving_1 = $request->reason_leaving_1;
            $PastEmp->salary_1 = $request->salary_1;

            $PastEmp->from_date_2 = $request->from_date_2;
            $PastEmp->to_date_2 = $request->to_date_2;
            $PastEmp->name_address_employer_2 = $request->name_address_employer_2;
            $PastEmp->phone_number_2 = $request->phone_number_2;
            $PastEmp->job_2 = $request->job_2;
            $PastEmp->reason_leaving_2 = $request->reason_leaving_2;
            $PastEmp->salary_2 = $request->salary_2;

            $PastEmp->from_date_3 = $request->from_date_3;
            $PastEmp->to_date_3 = $request->to_date_3;
            $PastEmp->name_address_employer_3 = $request->name_address_employer_3;
            $PastEmp->phone_number_3 = $request->phone_number_3;
            $PastEmp->job_3 = $request->job_3;
            $PastEmp->reason_leaving_3 = $request->reason_leaving_3;
            $PastEmp->salary_3 = $request->salary_3;
            $PastEmp->applicant_id = $userID;


            //user profile
            $UserProfile->full_name = $request->full_name;
            $UserProfile->phone = $request->phone;
            $UserProfile->applicant_id = $userID;


            //emergency
            $Emergency->emergency_address = $request->emergency_address;
            $Emergency->emergency_city = $request->emergency_city;
            $Emergency->emergency_state = $request->emergency_state;
            $Emergency->emergency_zip = $request->emergency_zip;
            $Emergency->applicant_id = $userID;


            Profile::firstOrCreate(
                ['applicant_id' => $userID],
                [
                    'full_name' => $request->full_name,
                    'phone' => $request->phone,
                    'applicant_id' =>  $userID
                ]
            );


            Emergency::firstOrCreate(
                ['applicant_id' => $userID],
                [
                    'emergency_address' => $request->emergency_address,
                    'emergency_city' => $request->emergency_city,
                    'emergency_state' => $request->emergency_state,
                    'emergency_zip' => $request->emergency_zip,
                    'applicant_id' => $userID,
                ]
            );

            PastEmpInfo::firstOrCreate(
                ['applicant_id' => $userID],
                [
                    'from_date_1'   => $request->from_date_1,
                    'to_date_1' => $request->to_date_1,
                    'name_address_employer_1'   => $request->name_address_employer_1,
                    'phone_number_1'    => $request->phone_number_1,
                    'job_1' => $request->job_1,
                    'reason_leaving_1'  => $request->reason_leaving_1,
                    'salary_1' => $request->salary_1,

                    'from_date_2'   => $request->from_date_2,
                    'to_date_2' => $request->to_date_2,
                    'name_address_employer_2'   => $request->name_address_employer_2,
                    'phone_number_2'    => $request->phone_number_2,
                    'job_2' => $request->job_2,
                    'reason_leaving_2'  => $request->reason_leaving_2,
                    'salary_2' => $request->salary_2,

                    'from_date_3'   => $request->from_date_3,
                    'to_date_3' => $request->to_date_3,
                    'name_address_employer_3'   => $request->name_address_employer_3,
                    'phone_number_3'    => $request->phone_number_3,
                    'job_3' => $request->job_3,
                    'reason_leaving_3'  => $request->reason_leaving_3,
                    'salary_3' => $request->salary_3,
                    'applicant_id'  => $userID
                ]
            );

            AcademicTrades::firstOrCreate(
                ['applicant_id' => $userID],
                [
                    'edu_current_name_location_school' => $request->edu_current_name_location_school,
                    'edu_current_number_years' => $request->edu_current_number_years,
                    'edu_current_did_graduate' => $request->edu_current_did_graduate,
                    'edu_current_subjects_studied' => $request->edu_current_subjects_studied,
                    'edu_last_name_location_school' => $request->edu_last_name_location_school,
                    'edu_last_number_years' => $request->edu_last_number_years,
                    'edu_last_did_graduate' => $request->edu_last_did_graduate,
                    'edu_last_subjects_studied' => $request->edu_last_subjects_studied,
                    'trades_current_name_location_school' => $request->trades_current_name_location_school,
                    'trades_current_number_years' => $request->trades_current_number_years,
                    'trades_current_did_graduate' => $request->trades_current_number_years,
                    'trades_current_subjects_studied' => $request->trades_current_subjects_studied,
                    'trades_last_current_name_location_school' => $request->trades_last_current_name_location_school,
                    'trades_last_current_number_years' => $request->trades_last_current_number_years,
                    'trades_last_current_did_graduate' => $request->trades_last_current_did_graduate,
                    'trades_last_subjects_studied' => $request->trades_last_subjects_studied,
                    'applicant_id' => $userID
                ]
            );


            // check if present address is same as permanent address
            if ($request->input('present_permanent_address') == 'No') {
                PremanentAddress::firstOrCreate(
                    ['applicant_id' => $userID],
                    [
                        'permanent_address' => $request->permanent_address,
                        'permanent_city' => $request->permanent_city,
                        'permanent_state' => $request->permanent_state,
                        'permanent_zip' => $request->permanent_zip,
                        'applicant_id' => $userID
                    ]
                );
            } else {
                PremanentAddress::firstOrCreate(
                    ['applicant_id' => $userID],
                    [
                        'permanent_address' => $request->present_address,
                        'permanent_city' => $request->present_city,
                        'permanent_state' => $request->present_state,
                        'permanent_zip' => $request->present_zip,
                        'applicant_id' => $userID
                    ]
                );
            }

            PresentAdrress::firstOrCreate(
                ['applicant_id' => $userID],
                [
                    'present_address' => $request->present_address,
                    'present_city' => $request->present_city,
                    'present_state' => $request->present_state,
                    'present_zip' => $request->present_zip,
                    'applicant_id' => $userID,
                ]
            );

            EmploymentApplication::firstOrCreate(
                ['applicant_id' => $userID],
                [
                    'SSN' => $request->SSN,
                    'employee_hire_date' => $request->employee_hire_date,
                    'furnish_work' => $request->furnish_work,
                    'employment_desired' => $request->employment_desired,
                    'position' => $request->position,
                    'date_start' => $request->date_start,
                    'salary' => $request->salary,
                    'employed_now' => $request->employed_now,
                    'inqure_present_employer' => $request->inqure_present_employer,
                    'applied_before' => $request->applied_before,
                    'where' => $request->where,
                    'when' => $request->when,
                    'on_layoff_subject_to_recall' => $request->on_layoff_subject_to_recall,
                    'travel_if_required' => $request->travel_if_required,
                    'relocate_if_required' => $request->relocate_if_required,
                    'overtime_if_required' => $request->overtime_if_required,
                    'attendance_requirements_position' => $request->attendance_requirements_position,
                    'bonded' => $request->bonded,
                    'convicted' => $request->convicted,
                    'explain_convicted' => $request->explain_convicted,
                    'drivers_license' => $request->drivers_license,
                    'drivers_license_state' => $request->drivers_license_state,
                    'special_skills_qualifications' => $request->special_skills_qualifications,
                    'applicant_id' => $userID
                ]
            );

            Reference::firstOrCreate(
                ['applicant_id' => $userID],
                [
                    'reference_name_1' => $request->reference_name_1,
                    'reference_address_1' => $request->reference_address_1,
                    'reference_phone_1' => $request->reference_phone_1,
                    'reference_years_acquainted_1' => $request->reference_years_acquainted_1,
                    'reference_name_2' => $request->reference_name_2,
                    'reference_address_2' => $request->reference_address_2,
                    'reference_phone_2' => $request->reference_phone_2,
                    'reference_years_acquainted_2' => $request->reference_years_acquainted_2,
                    'reference_name_3' => $request->reference_name_3,
                    'reference_address_3' => $request->reference_address_3,
                    'reference_phone_3' => $request->reference_phone_3,
                    'reference_years_acquainted_3' => $request->reference_years_acquainted_3,
                    'applicant_id' => $userID
                ]
            );

            $LangInsert = Language::firstOrCreate(
                ['applicant_id' => $userID],
                [
                    'language_1' => $request->language_1,
                    'read_write_1' => $request->read_write_1,
                    'read_speak_1' => $request->read_speak_1,
                    'speak_only_1' => $request->speak_only_1,
                    'language_2' => $request->language_2,
                    'read_write_2' => $request->read_write_2,
                    'read_speak_2' => $request->read_speak_2,
                    'speak_only_2' => $request->speak_only_2,
                    'applicant_id' => $userID
                ]
            );

            if ($LangInsert) {
                //upload and save the Signature

                $signatureData = $request->input('signature');

                if ($request->filled('signature')) {
                }
                $signatureParts = explode(',', $signatureData);
                $signatureEncoded = $signatureParts[1]; // Extract the base64-encoded part

                $signatureBinary = base64_decode($signatureEncoded);

                // Generate a unique filename for the signature file
                $signatureName = time() . '.png';

                // Save the signature file to disk using the Storage facade
                Storage::put('public/signature/' . $signatureName, $signatureBinary);

                $Signature->signature = $signatureName;
                $Signature->applicant_id = $userID;
                $Signature->date_signed = now();

                Signature::firstOrCreate(
                    ['applicant_id' => $userID],
                    [
                        'signature' => $signatureName,
                        'date_signed' => $request->date_signed,
                        'applicant_id' => $userID
                    ]
                );
            }

            DB::commit();

            return response()->json(['message' => 'Application Submitted Successfully'], 200);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }
}
