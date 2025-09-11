<?php

namespace App\Http\Controllers;

use App\Models\AcademicTrades;
use App\Models\c;
use App\Models\Emergency;
use App\Models\EmploymentApplication;
use App\Models\Files;
use App\Models\Language;
use App\Models\PastEmpInfo;
use App\Models\PremanentAddress;
use App\Models\PresentAdrress;
use App\Models\Profile;
use App\Models\Reference;
use App\Models\Signature;
use App\Services\UserProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmploymentApplicationController extends Controller
{
    protected $userProfileService;

    public function __construct(UserProfileService $userProfileService)
    {
        $this->userProfileService = $userProfileService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(UserProfileService $userProfileService)
    {
        $profileData = $userProfileService->getUserProfileData();

        $userID = Auth::id();

        $academic = DB::table('academic_trades_business')->where('applicant_id', $userID)->get();
        $files = DB::table('applicant_files')->where('applicant_id', $userID)->get();
        $emergency = DB::table('emergency')->where('applicant_id', $userID)->get();
        $emp_applications = DB::table('employments_applications')->where('applicant_id', $userID)->get();
        $language = DB::table('language')->where('applicant_id', $userID)->get();
        $past_emp_info = DB::table('past_employment_info')->where('applicant_id', $userID)->get();
        $permanent_addr = DB::table('permanent_address')->where('applicant_id', $userID)->get();
        $present_addr = DB::table('present_address')->where('applicant_id', $userID)->get();
        $reference = DB::table('reference')->where('applicant_id', $userID)->get();
        $sig_nature = DB::table('signature')->where('applicant_id', $userID)->get();
        $users_profile = DB::table('users_profile')->where('applicant_id', $userID)->get();
        $users = DB::table('users')->where('id', $userID)->get();


        foreach($users_profile as $item){
            $full_name = $item->full_name;
            $phone = $item->phone;
            $user_avatar = $item->user_avatar;

            $usersProfileData[] = [
                'full_name' => $full_name,
                'phone' => $phone,
                'user_avatar' => $user_avatar
            ];
        }

        foreach($users as $item){
            $email = $item->email;
            $usersData[] = [
                'email' => $email
            ];
        }

        foreach($emp_applications as $item){
            $SSN = $item->SSN;
            $employee_hire_date = $item->employee_hire_date;
            $furnish_work = $item->furnish_work;
            $employment_desired = $item->employment_desired;
            $position = $item->position;
            $date_start = $item->date_start;
            $salary = $item->salary;
            $employed_now = $item->employed_now;
            $inqure_present_employer = $item->inqure_present_employer;
            $applied_before = $item->applied_before;
            $where = $item->where;
            $when = $item->when;
            $on_layoff_subject_to_recall = $item->on_layoff_subject_to_recall;
            $travel_if_required = $item->travel_if_required;
            $relocate_if_required = $item->relocate_if_required;
            $overtime_if_required = $item->overtime_if_required;
            $attendance_requirements_position = $item->attendance_requirements_position;
            $bonded = $item->bonded;
            $convicted = $item->convicted;
            $explain_convicted = $item->explain_convicted;
            $drivers_license = $item->drivers_license;
            $drivers_license_state = $item->drivers_license_state;
            $special_skills_qualifications = $item-> special_skills_qualifications;

            $empApplicationsData[] = [
                'SSN' => $SSN,
                'employee_hire_date' => $employee_hire_date,
                'furnish_work' => $furnish_work,
                'employment_desired' => $employment_desired,
                'position' => $position,
                'date_start' => $date_start,
                'salary' => $salary,
                'employed_now' => $employed_now,
                'inqure_present_employer' => $inqure_present_employer,
                'applied_before' => $applied_before,
                'where' => $where,
                'when' => $when,
                'on_layoff_subject_to_recall' => $on_layoff_subject_to_recall,
                'travel_if_required' => $travel_if_required,
                'relocate_if_required' => $relocate_if_required,
                'overtime_if_required' => $overtime_if_required,
                'attendance_requirements_position' => $attendance_requirements_position,
                'bonded' => $bonded,
                'convicted' => $convicted,
                'explain_convicted' => $explain_convicted,
                'drivers_license' => $drivers_license,
                'drivers_license_state' => $drivers_license_state,
                'special_skills_qualifications' => $special_skills_qualifications
            ];
        }

        foreach($sig_nature as $item){
            $signature = $item->signature;
            $date_signed = $item->date_signed;

            $signatureData[] = [
                'signature' => $signature,
                'date_signed' => $date_signed
            ];
        }

        foreach($language as $item){
            $language_1 = $item->language_1;
            $read_write_1 = $item->read_write_1;
            $read_speak_1 = $item->read_speak_1;
            $speak_only_1 = $item->speak_only_1;

            $language_2 = $item->language_2;
            $read_write_2 = $item->read_write_2;
            $read_speak_2 = $item->read_speak_2;
            $speak_only_2 = $item->speak_only_2;

            $languageData[] = [
                'language_1' => $language_1,
                'read_write_1' => $read_write_1,
                'read_speak_1' => $read_speak_1,
                'speak_only_1' => $speak_only_1,

                'language_2' => $language_2,
                'read_write_2' => $read_write_2,
                'read_speak_2' => $read_speak_2,
                'speak_only_2' => $speak_only_2
            ];
        }

        foreach($reference as $item){
            $reference_name_1 = $item->reference_name_1;
            $reference_address_1 = $item->reference_address_1;
            $reference_phone_1 = $item->reference_phone_1;
            $reference_years_acquainted_1 = $item->reference_years_acquainted_1;

            $reference_name_2 = $item->reference_name_2;
            $reference_address_2 = $item->reference_address_2;
            $reference_phone_2 = $item->reference_phone_2;
            $reference_years_acquainted_2 = $item->reference_years_acquainted_2;

            $reference_name_3 = $item->reference_name_3;
            $reference_address_3 = $item->reference_address_3;
            $reference_phone_3 = $item->reference_phone_3;
            $reference_years_acquainted_3 = $item->reference_years_acquainted_3;

            $referenceData[] = [
                'reference_name_1' => $reference_name_1,
                'reference_address_1' => $reference_address_1,
                'reference_phone_1' => $reference_phone_1,
                'reference_years_acquainted_1' => $reference_years_acquainted_1,

                'reference_name_2' => $reference_name_2,
                'reference_address_2' => $reference_address_2,
                'reference_phone_2' => $reference_phone_2,
                'reference_years_acquainted_2' => $reference_years_acquainted_2,

                'reference_name_3' => $reference_name_3,
                'reference_address_3' => $reference_address_3,
                'reference_phone_3' => $reference_phone_3,
                'reference_years_acquainted_3' => $reference_years_acquainted_3
            ];
        }

        foreach($permanent_addr as $item){
            $permanent_address = $item->permanent_address;
            $permanent_city = $item->permanent_city;
            $permanent_state = $item->permanent_state;
            $permanent_zip = $item->permanent_zip;

            $permanentAddressData[] = [
                'permanent_address' =>$permanent_address,
                'permanent_city' =>$permanent_city,
                'permanent_state' =>$permanent_state,
                'permanent_zip' =>$permanent_zip
            ];
        }

        foreach($present_addr as $item){
            $present_address = $item->present_address;
            $present_city = $item->present_city;
            $present_state = $item->present_state;
            $present_zip = $item->present_zip;

            $presentAddressData[] = [
                'present_address' =>$present_address,
                'present_city' =>$present_city,
                'present_state' =>$present_state,
                'present_zip' =>$present_zip
            ];
        }


        foreach($past_emp_info as $item){

            $from_date_1 = $item->from_date_1;
            $to_date_1 = $item->to_date_1;
            $name_address_employer_1 = $item->name_address_employer_1;
            $phone_number_1 = $item->phone_number_1;
            $job_1 = $item->job_1;
            $reason_leaving_1 = $item->reason_leaving_1;
            $salary_1 = $item->salary_1;

            $from_date_2 = $item->from_date_2;
            $to_date_2 = $item->to_date_2;
            $name_address_employer_2 = $item->name_address_employer_2;
            $phone_number_2 = $item->phone_number_2;
            $job_2 = $item->job_2;
            $reason_leaving_2 = $item->reason_leaving_2;
            $salary_2 = $item->salary_2;

            $from_date_3 = $item->from_date_3;
            $to_date_3 = $item->to_date_3;
            $name_address_employer_3 = $item->name_address_employer_3;
            $phone_number_3 = $item->phone_number_3;
            $job_3 = $item->job_3;
            $reason_leaving_3 = $item->reason_leaving_3;
            $salary_3 = $item->salary_3;

            $past_emp_data[] = [
                'from_date_1' => $from_date_1,
                'to_date_1' => $to_date_1,
                'name_address_employer_1' => $name_address_employer_1,
                'phone_number_1' => $phone_number_1,
                'job_1' => $job_1,
                'reason_leaving_1' => $reason_leaving_1,
                'salary_1' => $salary_1,
                
                'from_date_2' => $from_date_2,
                'to_date_2' => $to_date_2,
                'name_address_employer_2' => $name_address_employer_2,
                'phone_number_2' => $phone_number_2,
                'job_2' => $job_2,
                'reason_leaving_2' => $reason_leaving_2,
                'salary_2' => $salary_2,

                'from_date_3' => $from_date_3,
                'to_date_3' => $to_date_3,
                'name_address_employer_3' => $name_address_employer_3,
                'phone_number_3' => $phone_number_3,
                'job_3' => $job_3,
                'reason_leaving_3' => $reason_leaving_3,
                'salary_3' => $salary_3
            ];

        }

        foreach($academic as $item){
            $edu_current_name_location_school = $item->edu_current_name_location_school;
            $edu_current_number_years = $item->edu_current_number_years;
            $edu_current_did_graduate = $item->edu_current_did_graduate;
            $edu_current_subjects_studied = $item->edu_current_subjects_studied;
            $edu_last_name_location_school = $item->edu_last_name_location_school;
            $edu_last_number_years = $item->edu_last_number_years;
            $edu_last_did_graduate = $item->edu_last_did_graduate;
            $edu_last_subjects_studied = $item->edu_last_subjects_studied;
            $trades_current_name_location_school = $item->trades_current_name_location_school;
            $trades_current_number_years = $item->trades_current_number_years;
            $trades_current_did_graduate = $item->trades_current_did_graduate;
            $trades_current_subjects_studied = $item->trades_current_subjects_studied;
            $trades_last_current_name_location_school = $item->trades_last_current_name_location_school;
            $trades_last_current_number_years = $item->trades_last_current_number_years;
            $trades_last_current_did_graduate = $item->trades_last_current_did_graduate;
            $trades_last_subjects_studied = $item->trades_last_subjects_studied;

            $academicData[] = [
            'edu_current_name_location_school' => $edu_current_name_location_school,
            'edu_current_number_years' => $edu_current_number_years,
            'edu_current_did_graduate' => $edu_current_did_graduate,
            'edu_current_subjects_studied' => $edu_current_subjects_studied,
            'edu_last_name_location_school' => $edu_last_name_location_school,
            'edu_last_number_years' => $edu_last_number_years,
            'edu_last_did_graduate' => $edu_last_did_graduate,
            'edu_last_subjects_studied' => $edu_last_subjects_studied,
            'trades_current_name_location_school' => $trades_current_name_location_school,
            'trades_current_number_years' => $trades_current_number_years,
            'trades_current_did_graduate' => $trades_current_did_graduate,
            'trades_current_subjects_studied' => $trades_current_subjects_studied,
            'trades_last_current_name_location_school' => $trades_last_current_name_location_school,
            'trades_last_current_number_years' => $trades_last_current_number_years,
            'trades_last_current_did_graduate' => $trades_last_current_did_graduate,
            'trades_last_subjects_studied' => $trades_last_subjects_studied,
            ];
        }

        foreach($emergency as $item){
            // access columns of each item
            $emergency_address = $item->emergency_address;
            $emergency_city = $item->emergency_city;
            $emergency_state = $item->emergency_state;
            $emergency_zip = $item->emergency_zip;

            // store values in array
            $emergencyData[] = [
                'emergency_address' => $emergency_address,
                'emergency_city' => $emergency_city,
                'emergency_state' => $emergency_state,
                'emergency_zip' => $emergency_zip
            ];
        }

        return view('dashboard.employment-application.index', 
        [
            'emergencyData' => $emergencyData,
            'academicData' => $academicData,
            'past_emp_data' => $past_emp_data,
            'presentAddressData' => $presentAddressData,
            'permanentAddressData' => $permanentAddressData,
            'referenceData' => $referenceData,
            'languageData' => $languageData,
            'signatureData' => $signatureData,
            'empApplicationsData' => $empApplicationsData,
            'usersData' => $usersData,
            'usersProfileData' => $usersProfileData,
            'profileData' => $profileData
        ]
    );
    
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // validate form data
        $request->validate([
        'SSN'                                           => 'required|regex:/^\d{3}-\d{2}-\d{4}$/',
        'full_name'                                     => 'required|regex:/^[a-zA-Z., -]/',
        'furnish_work'                                  => 'required',
        // 'employment_desired'                         => 'required',
        'position'                                      => 'required',
        'date_start'                                    => 'required',
        'salary'                                        => 'required',
        'employed_now'                                  => 'required',
        'inqure_present_employer'                       => 'required',
        'applied_before'                                => 'required',
        'where'                                         => 'required',
        'when'                                          => 'required',
        'on_layoff_subject_to_recall'                   => 'required',
        'travel_if_required'                            => 'required',
        'relocate_if_required'                          => 'required',
        'overtime_if_required'                          => 'required',
        'attendance_requirements_position'              => 'required',
        'bonded'                                        => 'required',
        'convicted'                                     => 'required',
        // 'explain_convicted'                          => 'required',
        'drivers_license'                               => 'required',
        'drivers_license_state'                         => 'required',
        'special_skills_qualifications'                 => 'required',

        'phone'                                         => 'required|regex:/^\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}$/',

        'present_address'                               => 'required',
        'present_city'                                  => 'required',
        'present_state'                                 => 'required',
        'present_zip'                                   => 'required|regex:/^\d{5}(-\d{4})?$/',

        'present_permanent_address'                     => 'required',

        // permanent address table
        'permanent_address' => $request->input('present_permanent_address') == 'No' ? 'required|string' : '',
        'permanent_city' => $request->input('present_permanent_address') == 'No' ? 'required|string' : '',
        'permanent_state' => $request->input('present_permanent_address') == 'No' ? 'required|string' : '',
        'permanent_zip' => $request->input('present_permanent_address') == 'No' ? 'required|regex:/^\d{5}(-\d{4})?$/' : '',

        'edu_current_name_location_school'              => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
        'edu_current_number_years'                      => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
        'edu_current_did_graduate'                      => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
        'edu_current_subjects_studied'                  => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
        'edu_last_name_location_school'                 => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
        'edu_last_number_years'                         => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
        'edu__last_did_graduate'                        => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
        'edu_last_subjects_studied'                     => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
        'trades_current_name_location_school'           => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
        'trades_current_number_years'                   => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
        'trades_current_did_graduate'                   => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
        'trades_current_subjects_studied'               => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
        'trades_last_current_name_location_school'      => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
        'trades_last_current_number_years'              => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
        'trades_last_current_did_graduate'              => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',
        'trades_last_subjects_studied'                  => 'required|regex:/^[a-zA-Z 0-9\s,.-]+$/',

        'from_date_1'                                   => 'required',
        'to_date_1'                                     => 'required',
        'name_address_employer_1'                       => 'required|regex:/^[a-zA-Z 0-9,. -]+$/',
        'phone_number_1'                                => 'required|regex:/^\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}$/',
        'job_1'                                         => 'required|regex:/^[a-zA-Z]+$/',
        'reason_leaving_1'                              => 'required',
        'from_date_2',
        'to_date_2',
        'name_address_employer_2',
        'phone_number_2',
        'job_2',
        'reason_leaving_2',
        'from_date_3',
        'to_date_3',
        'name_address_employer_3',
        'phone_number_3',
        'job_3',
        'reason_leaving_3',

        'reference_name_1'                      => 'required|regex:/^[a-zA-Z]+$/',
        'reference_address_1'                   => 'required|regex:/^[a-zA-Z]+$/',
        'reference_phone_1'                     => 'required|regex:/^\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}$/',
        'reference_years_acquainted_1'          => 'required|regex:/^[a-zA-Z0-9]+$/',
        'reference_name_2'                      => 'required|regex:/^[a-zA-Z]+$/',
        'reference_address_2'                   => 'required|regex:/^[a-zA-Z]+$/',
        'reference_phone_2'                     => 'required|regex:/^\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}$/',
        'reference_years_acquainted_2'          => 'required|regex:/^[a-zA-Z]+$/',
        'reference_name_3'                      => 'required|regex:/^[a-zA-Z]+$/',
        'reference_address_3'                   => 'required|regex:/^[a-zA-Z]+$/',
        'reference_phone_3'                     => 'required|regex:/^\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}$/',
        'reference_years_acquainted_3'          => 'required|regex:/^[a-zA-Z0-9]+$/',

        'language_1'                            => 'required|regex:/^[a-zA-Z]+$/',
        'read_write_1'                          => 'required|regex:/^[a-zA-Z]+$/',
        'read_speak_1'                          => 'required|regex:/^[a-zA-Z]+$/',
        'speak_only_1'                          => 'required|regex:/^[a-zA-Z]+$/',
        'language_2',
        'read_write_2',
        'read_speak_2',
        'speak_only_2',

        'emergency_address'                     => 'required',
        'emergency_city'                        => 'required',
        'emergency_state'                       => 'required',
        'emergency_zip'                         => 'required',

        'social_security_card'                  => 'required|regex:/^(pdf|jpe?g|png)$/i',
        'covid_proof'                           => 'required|regex:/^(pdf|jpe?g|png)$/i',
        'pca_hha_certificate'                   => 'required|regex:/^(pdf|jpe?g|png)$/i',
        'proof_ppd'                             => 'required|regex:/^(pdf|jpe?g|png)$/i',
        'cpr_certificate'                       => 'required|regex:/^(pdf|jpe?g|png)$/i',

        'signature'                             => 'required',
        'date_signed'                           => 'required'

        ]);


        $EmpApp = new EmploymentApplication();
        $AcadTrad = new AcademicTrades();
        $AppFiles = new Files();
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
        $Reference->save();

        // employment application
        $EmpApp->SSN = $request->SSN;
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
        $EmpApp->save();

        // present address
        $PresAddr->present_address = $request->present_address;
        $PresAddr->present_city = $request->present_city;
        $PresAddr->present_state = $request->present_state;
        $PresAddr->present_zip  = $request->present_zip;
        $PresAddr->save();

        if($request->input('present_permanent_address') == 'Yes')
        {
            // permanent address
            $PermAddr->permanent_address = $request->present_address;
            $PermAddr->permanent_city= $request->present_city;
            $PermAddr->permanent_state = $request->present_state;
            $PermAddr->permanent_zip = $request->present_zip;
            // $PermAddr->applicant_id = $userID;
        }
        else
        {
            // permanent address
            $PermAddr->permanent_address = $request->permanent_address;
            $PermAddr->permanent_city= $request->permanent_city;
            $PermAddr->permanent_state = $request->permanent_state;
            $PermAddr->permanent_zip = $request->permanent_zip;
            // $PermAddr->applicant_id = $userID;
        }

        // Academic, Education, Trades Business
        $AcadTrad->edu_current_name_location_school = $request->edu_current_name_location_school;
        $AcadTrad->edu_current_number_years = $request->edu_current_number_years;
        $AcadTrad->edu_current_did_graduate = $request->edu_current_did_graduate;
        $AcadTrad->edu_current_subjects_studied = $request->edu_current_subjects_studied;
        $AcadTrad->edu_last_name_location_school = $request->edu_last_name_location_school;
        $AcadTrad->edu_last_number_years = $request->edu_last_number_years;
        $AcadTrad->edu__last_did_graduate = $request->edu__last_did_graduate;
        $AcadTrad->edu_last_subjects_studied = $request->edu_last_subjects_studied;

        $AcadTrad->trades_current_name_location_school = $request->trades_current_name_location_school;
        $AcadTrad->trades_current_number_years = $request->trades_current_number_years;
        $AcadTrad->trades_current_did_graduate = $request->trades_current_did_graduate;
        $AcadTrad->trades_current_subjects_studied = $request->trades_current_subjects_studied;
        $AcadTrad->trades_last_current_name_location_school = $request->trades_last_current_name_location_school;
        $AcadTrad->trades_last_current_number_years = $request->trades_last_current_number_years;
        $AcadTrad->trades_last_current_did_graduate = $request->trades_last_current_did_graduate;
        $AcadTrad->trades_last_subjects_studied = $request->trades_last_subjects_studied;
        $AcadTrad->save();

        // past employment info
        $PastEmp->from_date_1 = $request->from_date_1;
        $PastEmp->to_date_1 = $request->to_date_1;
        $PastEmp->name_address_employer_1 = $request->name_address_employer_1;
        $PastEmp->phone_number_1 = $request->phone_number_1;
        $PastEmp->job_1 = $request->job_1;
        $PastEmp->reason_leaving_1 = $request->reason_leaving_1;

        $PastEmp->from_date_2 = $request->from_date_2;
        $PastEmp->to_date_2 = $request->to_date_2;
        $PastEmp->name_address_employer_2 = $request->name_address_employer_2;
        $PastEmp->phone_number_2 = $request->phone_number_2;
        $PastEmp->job_2 = $request->job_2;
        $PastEmp->reason_leaving_2 = $request->reason_leaving_2;

        $PastEmp->from_date_3 = $request->from_date_3;
        $PastEmp->to_date_3 = $request->to_date_3;
        $PastEmp->name_address_employer_3 = $request->name_address_employer_3;
        $PastEmp->phone_number_3 = $request->phone_number_3;
        $PastEmp->job_3 = $request->job_3;
        $PastEmp->reason_leaving_3 = $request->reason_leaving_3;
        $PastEmp->save();

        // files
        $AppFiles->social_security_card = $request->social_security_card;
        $AppFiles->covid_proof = $request->covid_proof;
        $AppFiles->pca_hha_certificate = $request->pca_hha_certificate;
        $AppFiles->proof_ppd = $request->proof_ppd;
        $AppFiles->cpr_certificate = $request->cpr_certificate;
        $AppFiles->save();

        //user profile
        $UserProfile->full_name = $request->full_name;
        $UserProfile->phone = $request->phone;
        $UserProfile->save();

        //emergency
        $Emergency->emergency_address = $request->emergency_address;
        $Emergency->emergency_city = $request->emergency_city;
        $Emergency->emergency_state = $request->emergency_state;
        $Emergency->emergency_zip = $request->emergency_zip;
        $Emergency->save();

        //Signature
        $Signature->signature = $request->signature;
        $Signature->date_signed = $request->date_signed;
        $Signature->save();
    }

    /**
     * Display the specified resource.
     */
    public function show(c $c)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(c $c)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, c $c)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(c $c)
    {
        //
    }
}
