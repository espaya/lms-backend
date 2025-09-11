<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Services\AgencyNotes;
use App\Services\UserProfileService;
use Exception;
use Illuminate\Support\Facades\DB;

class ApplicantsController extends Controller
{
    protected $userProfileService;

    public function __construct(UserProfileService $userProfileService)
    {
        $this->userProfileService = $userProfileService;
    }

    public function getProfile($applicant_id)
    {
        $profile = DB::table('users_profile')->where('applicant_id', $applicant_id)->first();
        return $profile;
    }

    public function getUser($applicant_id)
    {
        $user = DB::table('users')->where('id', $applicant_id)->first();
        return $user;
    }

    public function index()
    {
        // page title
        $title = 'Applicants | 1staccess Home Care';

        $userID = 0;

        try 
        {
            $adminData =  $this->userProfileService->getUser();

            $adminAccount = $this->userProfileService->getAdminAccount();

            // get user 
            $user = DB::table('users')->where('role', 'USER')->orderBy('id', 'DESC')->get();
            $userData = [];
            foreach($user as $item){
                $userID = $item->id;
                $username = $item->name;
                $email = $item->email;

                $userData[] = [
                    'userID' => $userID,
                    'username' => $username,
                    'email' => $email 
                ];
            }

            // get users IDs
            $id = DB::table('users')->where('role', 'USER')->orderBy('id', 'DESC')->pluck('id');
            //get user profile
            $profile = DB::table('users_profile')->whereIn('applicant_id', $id)->orderBy('applicant_id', 'DESC')->paginate(10);
            // get applicant's address
            $address = DB::table('permanent_address')->whereIn('applicant_id', $id)->get();

            if($address->isEmpty())
            {
                $address = collect();
            }
            
            return view('admin.applicants.index', 
            [
                'adminAccount' => $adminAccount,

                'title' => $title, 
                'profile' => $profile, 
                'userData' => $userData, 
                'id' => $id, 
                'adminData' => $adminData,
                'address' => $address
            ]);
        }
        catch(Exception $ex)
        {
          dd($ex->getMessage());
        }
        
    }

    public function showByUsername($username)
    {
        $applicant = DB::table('users')->where('name', $username)->first();

        if($applicant)
        {
            return view('admin.applicants.show', ['applicant' => $applicant]);
        }
    }

    public function show($applicant_id)
    {
        $full_name = DB::table('users_profile')->where('applicant_id', $applicant_id)->pluck('full_name')->first();
        // page title
        $title =  $full_name . ' | 1staccess Home Care';

        $profileData = $this->getProfile($applicant_id);

        /* get all the application forms for this user based on applicant_id*/
        $attendance_tardiness = DB::table('attendance_tardiness')->where('applicant_id', $applicant_id)->get();

        $confidentiality_of_information = DB::table('confidentiality')->where('applicant_id', $applicant_id)->get();
        $criminal_history_search = DB::table('criminal_history_search')->where('applicant_id', $applicant_id)->get();

        $drug_testing_policy = DB::table('drug_testing_policy')->where('applicant_id', $applicant_id)->get();
        $disclaimer_waiver_liability = DB::table('disclaimer_waiver_liability')->where('applicant_id', $applicant_id)->get();

        $employee_safety = DB::table('employee_safety')->where('applicant_id', $applicant_id)->get();
        $emergency = DB::table('emergency')->where('applicant_id', $applicant_id)->get();
        $employee_agreement = DB::table('employee_agreement')->where('applicant_id', $applicant_id)->get();
        $employee_conduct = DB::table('employee_conduct')->where('applicant_id', $applicant_id)->get();
        $employee_dress_code = DB::table('employee_dress_code')->where('applicant_id', $applicant_id)->get();
        $employee_orientation = DB::table('employee_orientation')->where('applicant_id', $applicant_id)->get();
        $employee_reference_check = DB::table('employee_reference_check')->where('applicant_id', $applicant_id)->get();
        $employments_applications = DB::table('employments_applications')->where('applicant_id', $applicant_id)->get();
        

        $health_safety_agreement = DB::table('health_safety_agreement')->where('applicant_id', $applicant_id)->get();
        $home_health_aide = DB::table('home_health_aide')->where('applicant_id', $applicant_id)->get();

        $infection_control_agreement = DB::table('infection_control_agreement')->where('applicant_id', $applicant_id)->get();

        $language = DB::table('language')->where('applicant_id', $applicant_id)->get();

        $non_compete_agreement = DB::table('non_compete_agreement')->where('applicant_id', $applicant_id)->get();

        $policies_procedures = DB::table('policy_and_procedure')->where('applicant_id', $applicant_id)->get();

        $reporting = DB::table('reporting')->where('applicant_id', $applicant_id)->get();

        $sexual_harassment = DB::table('sexual_harassment')->where('applicant_id', $applicant_id)->get();
        $smoking_in_the_workplace = DB::table('smoking_in_the_workplace')->where('applicant_id', $applicant_id)->get();
        $sworn_disclosure_statement = DB::table('sworn_disclosure_statement')->where('applicant_id', $applicant_id)->get();
        $universal_precaution = DB::table('universal_precaution')->where('applicant_id', $applicant_id)->get();

        $verification = DB::table('verification')->where('applicant_id', $applicant_id)->get();

        $adminData =  $this->userProfileService->getUser();

        $adminAccount = $this->userProfileService->getAdminAccount();


        return view('admin.applicants.single-applicant', 
        [
            'adminAccount' => $adminAccount,

            'adminData' => $adminData,

            'applicant_id' => $applicant_id, 
            'title' => $title, 
            'full_name' => $full_name,

            'attendance_tardiness' => $attendance_tardiness,

            'profileData' => $profileData,

            'employee_safety' => $employee_safety,
            'confidentiality_of_information' => $confidentiality_of_information,

            'emergency' => $emergency,
            'employee_agreement' => $employee_agreement,

            'criminal_history_search' => $criminal_history_search,
            'drug_testing_policy' => $drug_testing_policy,
            'disclaimer_waiver_liability' => $disclaimer_waiver_liability,
            'employee_conduct' => $employee_conduct,
            'employee_dress_code' => $employee_dress_code,
            'employee_orientation' => $employee_orientation,
            'employee_reference_check' => $employee_reference_check,
            'employments_applications' => $employments_applications,
            'health_safety_agreement' => $health_safety_agreement,
            'home_health_aide' => $home_health_aide,
            'infection_control_agreement' => $infection_control_agreement,
            'language' => $language,
            'non_compete_agreement' => $non_compete_agreement,

            'policies_procedures' => $policies_procedures,

            'reporting' => $reporting,
            'sexual_harassment' => $sexual_harassment,
            'smoking_in_the_workplace' => $smoking_in_the_workplace,
            'sworn_disclosure_statement' => $sworn_disclosure_statement,
            'universal_precaution' => $universal_precaution,
            'verification' => $verification,

            ]
        );
    }

    public function viewEmployeeSafety($applicant_id, $id)
    {
        $adminData =  $this->userProfileService->getUser();

        $title = 'Employee Safety | 1staccess Home Care';

        $profileData = $this->getProfile($applicant_id);

        // Use first() to retrieve a single record
        $employee_safety = DB::table('employee_safety')->where('applicant_id', $applicant_id)->where('id', $id)->first();

        $employee_safety_data = [];

        $adminAccount = $this->userProfileService->getAdminAccount();

        // Check if $employee_safety is not null before processing
        if ($employee_safety) {
            $id = $employee_safety->id;
            $applicant_id = $employee_safety->applicant_id;
            $signature = $employee_safety->signature;
            $created_at = $employee_safety->created_at;
            $rep_signature = $employee_safety->rep_signature;

            $employee_safety_data[] = [
                'id' => $id,
                'applicant_id' => $applicant_id,
                'signature' => $signature,
                'created_at' => $created_at,
                'rep_signature' => $rep_signature
                
            ];
        }

        return view('admin.applicants.forms.employee-safety', [
            'adminAccount' => $adminAccount,
            'title' => $title,
            'applicant_id' => $applicant_id,
            'id' => $id,
            'employee_safety_data' => $employee_safety_data,
            'profileData' => $profileData,
            'adminData' => $adminData
        ]);
    }

    public function viewConfidentialityOfInformation($applicant_id, $id)
    {
        $title = 'Confidentiality of Information Agreement | 1staccess Home Care';

        $profileData = $this->getProfile($applicant_id);

        $confidentialityData = []; 

        $confidentiality_of_information = DB::table('confidentiality')->where('applicant_id', $applicant_id)->where('id', $id)->first();

        $adminData =  $this->userProfileService->getUser();

        $adminAccount = $this->userProfileService->getAdminAccount();

        if($confidentiality_of_information)
        {
            $confidentialityData[] = [
                'id' => $confidentiality_of_information->id,
                'applicant_id' => $confidentiality_of_information->applicant_id,
                'signature' => $confidentiality_of_information->signature,
                'created_at' => $confidentiality_of_information->created_at,
            ];
        }

        return view('admin.applicants.forms.confidentiality-of-information-agreement', 
        [
            'adminAccount' => $adminAccount,
            'title' => $title, 
            'applicant_id' => $applicant_id, 
            'id' => $id, 
            'confidentialityData' => $confidentialityData,
            'profileData' => $profileData,
            'adminData' => $adminData
        ]);
    }

    public function viewCriminalHistorySearch($applicant_id, $id)
    {
        $title = 'Criminal History Search Consent Form | 1staccess Home Care';

        $profileData = $this->getProfile($applicant_id);

        $criminal_history_search_data = [];

        $criminal_history_search = DB::table('criminal_history_search')->where('applicant_id', $applicant_id)->where('id', $id)->first();

        $adminData =  $this->userProfileService->getUser();

        $adminAccount = $this->userProfileService->getAdminAccount();
        
        if($criminal_history_search)
        {
            $id = $criminal_history_search->id;
            $applicant_id = $criminal_history_search->applicant_id;
            $signature = $criminal_history_search->signature;
            $created_at = $criminal_history_search->created_at;

            $criminal_history_search_data[] = [
                'id' => $id,
                'applicant_id' => $applicant_id,
                'signature' => $signature,
                'created_at' => $created_at,
            ];
        }

        return view('admin.applicants.forms.criminal-history-search', 
        [
            'adminAccount' => $adminAccount,
            'title' => $title, 
            'applicant_id' => $applicant_id, 
            'criminal_history_search_data' => $criminal_history_search_data,
            'profileData' => $profileData,
            'adminData' => $adminData
        ]);
    }

    public function viewDrugTestingPolicy($applicant_id, $id)
    {
        $title = 'Drug Testing Policy | 1staccess Home Care';

        $drug_testing_policy_data = [];

        $profileData = $this->getProfile($applicant_id);

        $adminData =  $this->userProfileService->getUser();

        $drug_testing_policy = DB::table('drug_testing_policy')->where('applicant_id', $applicant_id)->where('id', $id)->first();

        $adminAccount = $this->userProfileService->getAdminAccount();

        if($drug_testing_policy)
        {
            $id = $drug_testing_policy->id;
            $applicant_id = $drug_testing_policy->applicant_id;
            $signature = $drug_testing_policy->signature;
            $created_at = $drug_testing_policy->created_at;

            $drug_testing_policy_data[] = [
                'id' => $id,
                'applicant_id' => $applicant_id,
                'signature' => $signature,
                'created_at' => $created_at
            ];
        }

        return view('admin.applicants.forms.drug-testing-policy', 
        [
            'adminAccount' => $adminAccount,
            'title' => $title, 
            'drug_testing_policy_data' => $drug_testing_policy_data, 
            'id' => $id, 
            'applicant_id' => $applicant_id,
            'profileData' => $profileData,
            'adminData' => $adminData
        ]);
    }

    public function viewDisclaimerWaiverLiability($applicant_id, $id)
    {
        $title = 'Disclaimer and Waiver of Liability | 1staccess Home Care';

        $disclaimer_waiver_liability = DB::table('disclaimer_waiver_liability')->where('applicant_id', $applicant_id)->where('id', $id)
        ->first();

        $disclaimer_waiver_liability_data = [];

        $profileData = $this->getProfile($applicant_id);

        $adminData =  $this->userProfileService->getUser();

        $adminAccount = $this->userProfileService->getAdminAccount();

        if($disclaimer_waiver_liability)
        {
            $id = $disclaimer_waiver_liability->id;
            $applicant_id = $disclaimer_waiver_liability->applicant_id;
            $signature = $disclaimer_waiver_liability->signature;
            $created_at = $disclaimer_waiver_liability->created_at;
            $rep_signature = $disclaimer_waiver_liability->rep_signature;

            $disclaimer_waiver_liability_data[] = [
                'id' => $id,
                'applicant_id' => $applicant_id,
                'signature' => $signature,
                'created_at' => $created_at,
                'rep_signature' => $rep_signature
            ];
        }

        return view('admin.applicants.forms.disclaimer-waiver-liability', 
        [
            'adminAccount' => $adminAccount,
            'title' => $title, 
            'applicant_id' => $applicant_id, 
            'id' => $id, 
            'disclaimer_waiver_liability_data' => $disclaimer_waiver_liability_data,
            'profileData' => $profileData,
            'adminData' => $adminData
        ]);
    }

    public function viewEmployeeAgreement($applicant_id, $id)
    {
        $title = 'Employee Agreement | 1staccess Home Care';

        $profileData = $this->getProfile($applicant_id);

        $employee_agreement = DB::table('employee_agreement')->where('applicant_id', $applicant_id)->where('id', $id)->first();

        $employee_agreement_data = [];

        $adminData =  $this->userProfileService->getUser();

        $adminAccount = $this->userProfileService->getAdminAccount();

        if($employee_agreement)
        {
            $id = $employee_agreement->id;
            $applicant_id = $employee_agreement->applicant_id;
            $monday_hour = $employee_agreement->monday_hour;
            $tuesday_hour = $employee_agreement->tuesday_hour;
            $wednesday_hour = $employee_agreement->wednesday_hour;
            $thursday_hour = $employee_agreement->thursday_hour;
            $friday_hour = $employee_agreement->friday_hour;
            $saturday_hour = $employee_agreement->saturday_hour;
            $sunday_hour = $employee_agreement->sunday_hour;
            $time_off = $employee_agreement->time_off;
            $pay_per_hour = $employee_agreement->pay_per_hour;
            $other_agreements = $employee_agreement->other_agreements;
            $signature = $employee_agreement->signature;
            $created_at = $employee_agreement->created_at;
            $employer_signature = $employee_agreement->employer_signature;

            $employee_agreement_data[] = [
                'id' => $id,
                'applicant_id' => $applicant_id,
                'monday_hour' => $monday_hour,
                'tuesday_hour' => $tuesday_hour,
                'wednesday_hour' => $wednesday_hour,
                'thursday_hour' => $thursday_hour,
                'friday_hour' => $friday_hour,
                'saturday_hour' => $saturday_hour,
                'sunday_hour' => $sunday_hour,
                'time_off' => $time_off,
                'pay_per_hour' => $pay_per_hour,
                'other_agreements' => $other_agreements,
                'signature' => $signature,
                'created_at' => $created_at,
                'employer_signature' => $employer_signature 
            ]; 
        }

        return view('admin.applicants.forms.employee-agreement', 
        [
            'adminAccount' => $adminAccount,
            'title' => $title, 
            'applicant_id' => $applicant_id, 
            'id' => $id, 
            'employee_agreement_data' => $employee_agreement_data,
            'profileData'=> $profileData,
            'adminData' => $adminData
        ]);

    }

    public function viewEmployeeConduct($applicant_id, $id)
    {
        $profileData =  $this->getProfile($applicant_id);

        $title = 'Employee Notification of Policy: Employee Conduct | 1staccess Home Care';

        $employee_conduct = DB::table('employee_conduct')->where('applicant_id', $applicant_id)->where('id', $id)->first();

        $employee_conduct_data = [];

        $adminData =  $this->userProfileService->getUser();

        $adminAccount = $this->userProfileService->getAdminAccount();

        if($employee_conduct)
        {

            $id = $employee_conduct->id;
            $applicant_id = $employee_conduct->applicant_id;
            $signature = $employee_conduct->signature;
            $created_at = $employee_conduct->created_at;
            $hr_signature = $employee_conduct->hr_signature;
            $supervisor_signature = $employee_conduct->supervisor_signature;

            $employee_conduct_data[] = [
                'id' => $id,
                'applicant_id' => $applicant_id,
                'signature' => $signature,
                'created_at' => $created_at,
                'hr_signature' => $hr_signature,
                'supervisor_signature' => $supervisor_signature
            ];
        }

        return view('admin.applicants.forms.employee-conduct', 
        [
            'adminAccount' => $adminAccount,
            'id' => $id, 
            'applicant_id' => $applicant_id, 
            'title' => $title, 
            'employee_conduct_data' => $employee_conduct_data,
            'profileData' => $profileData,
            'adminData' => $adminData
        ]);
    }

    public function viewEmployeeDressCode($applicant_id, $id)
    {
        $profileData = $this->getProfile($applicant_id);

        $title = 'Employee Dress Code | 1staccess Home Care';

        $employee_dress_code = DB::table('employee_dress_code')->where('applicant_id', $applicant_id)->where('id', $id)->first();

        $employee_dress_code_data = [];

        $adminData =  $this->userProfileService->getUser();

        $adminAccount = $this->userProfileService->getAdminAccount();

        if($employee_dress_code)
        {
            $id = $employee_dress_code->id;
            $applicant_id = $employee_dress_code->applicant_id;
            $signature = $employee_dress_code->signature;
            $created_at = $employee_dress_code->created_at;

            $employee_dress_code_data[] = [
                'id' => $id,
                'applicant_id' => $applicant_id,
                'signature' => $signature,
                'created_at' => $created_at ,
            ];
        }

        return view('admin.applicants.forms.employee-dress-code', 
        [
            'adminAccount' => $adminAccount,
            'applicant_id' => $applicant_id, 
            'id' => $id, 
            'title' => $title, 
            'employee_dress_code_data' => $employee_dress_code_data,
            'profileData' => $profileData,
            'adminData' => $adminData
        ]);
    }

    public function viewEmployeeOrientation($applicant_id, $id)
    {
        $profileData = $this->getProfile($applicant_id);

        $title = 'Employee Orientation | 1staccess Home Care';

        $employee_orientation_data = [];

        $adminData =  $this->userProfileService->getUser();

        $position_hire_date = DB::table('employments_applications')->where('applicant_id', $applicant_id)->first();

        $employee_orientation = DB::table('employee_orientation')->where('applicant_id', $applicant_id)->where('id', $id)->first();

        $adminAccount = $this->userProfileService->getAdminAccount();

        if($employee_orientation)
        {
            $id = $employee_orientation->id;
            $applicant_id = $employee_orientation->applicant_id;
            $signature = $employee_orientation->signature;
            $dateOfOrientation = $employee_orientation->dateOfOrientation;
            $created_at = $employee_orientation->created_at;
            $hr_signature = $employee_orientation->hr_signature;

            $position = $position_hire_date->position;
            $date_of_hire = $position_hire_date->employee_hire_date;

            $employee_orientation_data[] = [
                'id' => $id,
                'applicant_id' => $applicant_id,
                'signature' => $signature,
                'dateOfOrientation' => $dateOfOrientation,
                'created_at' => $created_at,
                'position' => $position,
                'date_of_hire' => $date_of_hire,
                'hr_signature' => $hr_signature
            ];
        }

        return view('admin.applicants.forms.employee-orientation', 
        [
            'adminAccount' => $adminAccount,
            'applicant_id' => $applicant_id, 
            'id' => $id, 
            'title' => $title, 
            'employee_orientation_data' => $employee_orientation_data,
            'profileData' => $profileData,
            'adminData' => $adminData
        ]);
    }

    public function viewEmployeeReferenceCheck($applicant_id, $id)
    {
        $title = 'Employee Reference Check | 1staccess Home Care';

        $employee_reference_check = DB::table('employee_reference_check')->where('applicant_id', $applicant_id)->where('id', $id)->first();

        $profileData = $this->getProfile($applicant_id);

        $adminData =  $this->userProfileService->getUser();

        $adminAccount = $this->userProfileService->getAdminAccount();

        if($employee_reference_check)
        {
            $id = $employee_reference_check->id;
            $applicant_id = $employee_reference_check->applicant_id;
            $company_contacted = $employee_reference_check->company_contacted;
            $employer_name = $employee_reference_check->employer_name;
            $from_date = $employee_reference_check->from_date;
            $to_date = $employee_reference_check->to_date;
            $eligible_for_hire = $employee_reference_check->eligible_for_hire;
            $comments = $employee_reference_check->comments;
            $received_by = $employee_reference_check->received_by;
            $name_of_company = $employee_reference_check->name_of_company;
            $signature = $employee_reference_check->signature;
            $rep_signature = $employee_reference_check->rep_signature;
            $rep_title = $employee_reference_check->rep_title;
            $created_at = $employee_reference_check->created_at;

            $employee_reference_check_data[] = [
                'id' => $id,
                'applicant_id' => $applicant_id,
                'company_contacted' => $company_contacted,
                'employer_name' => $employer_name,
                'from_date' => $from_date,
                'to_date' => $to_date,
                'eligible_for_hire' => $eligible_for_hire,
                'comments' => $comments,
                'received_by' => $received_by,
                'name_of_company' => $name_of_company,
                'signature' => $signature,
                'rep_signature' => $rep_signature,
                'rep_title' => $rep_title,
                'created_at' => $created_at
            ];
        }

        return view('admin.applicants.forms.employee-reference-check', 
        [
            'adminAccount' => $adminAccount,
            'title' => $title, 
            'applicant_id' => $applicant_id, 
            'id' => $id, 
            'employee_reference_check_data' => $employee_reference_check_data,
            'profileData' => $profileData,
            'adminData' => $adminData
        ]);
    }

    public function viewHealthSafetyAgreement($applicant_id, $id)
    {
        $title = 'Health and Safety Agreement | 1staccess Home Care';

        $health_safety_agreement = DB::table('health_safety_agreement')->where('applicant_id', $applicant_id)->where('id', $id)->first();

        $health_safety_agreement_data = [];

        $profileData = $this->getProfile($applicant_id);

        $adminData =  $this->userProfileService->getUser();

        $adminAccount = $this->userProfileService->getAdminAccount();

        if($health_safety_agreement)
        {
            $id = $health_safety_agreement->id;
            $applicant_id = $health_safety_agreement->applicant_id;
            $signature = $health_safety_agreement->signature;
            $created_at = $health_safety_agreement->created_at;

            $agency_rep_signature = $health_safety_agreement->agency_rep_signature;

            $health_safety_agreement_data[] = [
                'id' => $id,
                'applicant_id' => $applicant_id,
                'signature' => $signature,
                'created_at' => $created_at,
                'agency_rep_signature' => $agency_rep_signature,
            ];
        }

        return view('admin.applicants.forms.health-safety-agreement', 
        [
            'adminAccount' => $adminAccount,
            'id' => $id, 
            'applicant_id' => $applicant_id, 
            'title' => $title, 
            'health_safety_agreement_data' => $health_safety_agreement_data,
            'profileData' => $profileData,
            'adminData' => $adminData
        ]);
    }

    public function viewHomeHealthAide($applicant_id, $id)
    {
        $title = 'Job Description: Home Health Aide | 1staccess Home Care';

        $home_health_aide = DB::table('home_health_aide')->where('applicant_id', $applicant_id)->where('id', $id)->first();

        $profileData = $this->getProfile($applicant_id);

        $adminData =  $this->userProfileService->getUser();

        $adminAccount = $this->userProfileService->getAdminAccount();

        $home_health_aide_data = [];

        if($home_health_aide)
        {
            $id = $home_health_aide->id;
            $applicant_id = $home_health_aide->applicant_id;
            $signature = $home_health_aide->signature;
            $created_at = $home_health_aide->created_at;

            $home_health_aide_data[] = [
                'id' => $id,
                'applicant_id' => $applicant_id,
                'signature' => $signature,
                'created_at' => $created_at,
            ];
        }

        return view('admin.applicants.forms.home-health-aide', 
        [
            'adminAccount' => $adminAccount,
            'id' => $id, 
            'applicant_id' => $applicant_id, 
            'home_health_aide_data' => $home_health_aide_data,
            'title' => $title,
            'profileData' => $profileData,
            'adminData' => $adminData
        ]);
    }

    public function viewInfectionControlAgreement($applicant_id, $id)
    {
        $title = 'Following Infection Control Agreement | 1staccess Home Care';

        $infection_control_agreement = DB::table('infection_control_agreement')->where('applicant_id', $applicant_id)
        ->where('id', $id)->first();

        $profileData = $this->getProfile($applicant_id);

        $infection_control_agreement_data = [];

        $adminData =  $this->userProfileService->getUser();

        $adminAccount = $this->userProfileService->getAdminAccount();

        if($infection_control_agreement)
        {
            $id = $infection_control_agreement->id;
            $applicant_id = $infection_control_agreement->applicant_id;
            $signature = $infection_control_agreement->signature;
            $created_at =$infection_control_agreement->created_at;

            $agency_rep_signature = $infection_control_agreement->agency_rep_signature;

            $infection_control_agreement_data[] = [
                'id' => $id,
                'applicant_id' => $applicant_id,
                'signature' => $signature,
                'created_at' => $created_at,

                'agency_rep_signature' => $agency_rep_signature
            ];
        }

        return view('admin.applicants.forms.infection-control-agreement', 
        [
            'adminAccount' => $adminAccount,
            'applicant_id' => $applicant_id, 
            'id' => $id, 
            'title' => $title, 
            'infection_control_agreement_data' => $infection_control_agreement_data,
            'profileData' => $profileData,
            'adminData' => $adminData
        ]);
    }

    public function viewNonCompeteAgreement($applicant_id, $id)
    {
        $title = 'Non Compete Agreement | 1staccess Home Care';

        $non_compete_agreement = DB::table('non_compete_agreement')->where('applicant_id', $applicant_id)->where('id', $id)->first();

        $non_compete_agreement_data = [];

        $profileData = $this->getProfile($applicant_id);

        $adminData =  $this->userProfileService->getUser();

        $adminAccount = $this->userProfileService->getAdminAccount();

        if($non_compete_agreement)
        {
            $id = $non_compete_agreement->id;
            $applicant_id = $non_compete_agreement->applicant_id;
            $signature = $non_compete_agreement->signature;
            $created_at = $non_compete_agreement->created_at;
            $agency_rep_signature = $non_compete_agreement->agency_rep_signature;

            $non_compete_agreement_data[] = [
                'id' => $id,
                'applicant_id' => $applicant_id,
                'signature' => $signature,
                'created_at' => $created_at,
                'agency_rep_signature' => $agency_rep_signature
            ];
        }

        return view('admin.applicants.forms.non-compete-agreement', 
        [
            'adminAccount' => $adminAccount,
            'id' => $id, 
            'applicant_id' => $applicant_id, 
            'title' => $title, 
            'non_compete_agreement_data' => $non_compete_agreement_data,
            'profileData' => $profileData,
            'adminData' => $adminData
        ]);
    }

    public function viewSexualHarassment($applicant_id, $id)
    {
        $title = 'Sexual Harassment | 1staccess Home Care';

        $sexual_harassment = DB::table('sexual_harassment')->where('applicant_id', $applicant_id)->where('id', $id)->first();

        $sexual_harassment_data = [];

        $profileData = $this->getProfile($applicant_id);

        $adminData =  $this->userProfileService->getUser();

        $adminAccount = $this->userProfileService->getAdminAccount();

        if($sexual_harassment)
        {
            $id = $sexual_harassment->id;
            $applicant_id = $sexual_harassment->applicant_id;
            $signature = $sexual_harassment->signature;
            $created_at = $sexual_harassment->created_at;

            $sexual_harassment_data[] = [
                'id' => $id,
                'applicant_id' => $applicant_id,
                'signature' => $signature,
                'created_at' => $created_at,
            ];
        }

        return view('admin.applicants.forms.sexual-harassment', 
        [
            'adminAccount' => $adminAccount,
            'id' => $id, 
            'applicant_id' => $applicant_id, 
            'title' => $title, 
            'sexual_harassment_data' => $sexual_harassment_data,
            'profileData' => $profileData,
            'adminData' => $adminData
        ]);
    }

    public function viewSmokingInTheWorkplace($applicant_id, $id)
    {
        $title = 'Employee Notification of Policy: Smoking In The Workplace | 1staccess Home Care';

        $smoking_in_the_workplace = DB::table('smoking_in_the_workplace')->where('applicant_id', $applicant_id)->where('id', $id)->first();

        $smoking_in_the_workplace_data = [];

        $profileData = $this->getProfile($applicant_id);

        $empData = DB::table('employments_applications')->where('applicant_id', $applicant_id)->first();

        $adminData =  $this->userProfileService->getUser();

        $adminAccount = $this->userProfileService->getAdminAccount();

        if($smoking_in_the_workplace)
        {
            $id = $smoking_in_the_workplace->id;
            $applicant_id = $smoking_in_the_workplace->applicant_id;
            $signature = $smoking_in_the_workplace->signature;
            $created_at = $smoking_in_the_workplace->created_at;
            $hire_date = $empData->employee_hire_date;
            $supervisor_signature = $smoking_in_the_workplace->supervisor_signature;
            $hr_signature = $smoking_in_the_workplace->hr_signature;

            $smoking_in_the_workplace_data[] = [
                'id' => $id,
                'applicant_id' => $applicant_id,
                'signature' => $signature,
                'created_at' => $created_at,
                'hire_date' => $hire_date,
                'supervisor_signature' => $supervisor_signature,
                'hr_signature' => $hr_signature
            ];
        }

        return view('admin.applicants.forms.smoking-in-the-workplace', 
        [
            'adminAccount' => $adminAccount,
            'id' => $id, 
            'applicant_id' => $applicant_id, 
            'title' => $title, 
            'smoking_in_the_workplace_data' => $smoking_in_the_workplace_data,
            'profileData' => $profileData,
            'adminData' => $adminData
        ]);
    }

    public function viewSwornDisclosureStatement($applicant_id, $id)
    {
        $title = 'Sworn Disclosure Statement | 1staccess Home Care';

        $sworn_disclosure_statement = DB::table('sworn_disclosure_statement')->where('applicant_id', $applicant_id)->where('id', $id)->first();

        $sworn_disclosure_statement_data = [];

        $empData = DB::table('employments_applications')->where('applicant_id', $applicant_id)->first();

        $profileData = $this->getProfile($applicant_id);

        $adminData =  $this->userProfileService->getUser();

        $adminAccount = $this->userProfileService->getAdminAccount();

        if($sworn_disclosure_statement)
        {
            $id = $sworn_disclosure_statement->id;
            $applicant_id = $sworn_disclosure_statement->applicant_id;
            $mailing_address = $sworn_disclosure_statement->mailing_address;
            $convicted_outside_commonwealth = $sworn_disclosure_statement->convicted_outside_commonwealth;
            $outside_commonwealth_specify = $sworn_disclosure_statement->outside_commonwealth_specify;
            $convicted_pending = $sworn_disclosure_statement->convicted_pending;
            $convicted_pending_specify = $sworn_disclosure_statement->convicted_pending_specify;
            $child_abuse = $sworn_disclosure_statement->child_abuse;
            $wit_signature = $sworn_disclosure_statement->wit_signature;
            $signature = $sworn_disclosure_statement->signature;
            $created_at = $sworn_disclosure_statement->created_at;
            $position = $empData->position;
            $SSN = $empData->SSN;

            $sworn_disclosure_statement_data[] = [
                'id' => $id,
                'applicant_id' => $applicant_id,
                'mailing_address' => $mailing_address,
                'convicted_outside_commonwealth' => $convicted_outside_commonwealth,
                'outside_commonwealth_specify' => $outside_commonwealth_specify,
                'convicted_pending' => $convicted_pending,
                'convicted_pending_specify' => $convicted_pending_specify,
                'child_abuse' => $child_abuse,
                'wit_signature' => $wit_signature,
                'signature' => $signature,
                'created_at' => $created_at,

                'position' => $position,
                'SSN' => $SSN
            ];
        }

        return view('admin.applicants.forms.sworn-disclosure-statement', 
        [
            'adminAccount' => $adminAccount,
            'id' => $id, 
            'applicant_id' => $applicant_id, 
            'title' => $title, 
            'sworn_disclosure_statement_data' => $sworn_disclosure_statement_data,
            'profileData' => $profileData,
            'adminData' => $adminData
        ]);
    }

    public function viewUniversalPrecautions($applicant_id, $id)
    {
        $title = 'Training Documentation on Universal Precautions | 1staccess Home Care';

        $universal_precaution = DB::table('universal_precaution')->where('applicant_id', $applicant_id)->where('id', $id)->first();

        $universal_precaution_data = [];

        $profileData = $this->getProfile($applicant_id);

        $adminData =  $this->userProfileService->getUser();

        $adminAccount = $this->userProfileService->getAdminAccount();

        if($universal_precaution)
        {
            $id = $universal_precaution->id;
            $applicant_id = $universal_precaution->applicant_id;
            $signature = $universal_precaution->signature;
            $created_at = $universal_precaution->created_at;

            $universal_precaution_data[] = [
                'id' => $id,
                'applicant_id' => $applicant_id,
                'signature' => $signature,
                'created_at' => $created_at
            ];
        }

        return view('admin.applicants.forms.universal-precautions', 
        [
            'adminAccount' => $adminAccount,
            'id' => $id, 
            'applicant_id' => $applicant_id, 
            'title' => $title, 
            'universal_precaution_data' => $universal_precaution_data,
            'profileData' => $profileData,
            'adminData' => $adminData
        ]);
    }

    public function viewVerification($applicant_id, $id)
    {
        $title = 'Verification of Professional License | 1staccess Home Care';

        $verification = DB::table('verification')->where('applicant_id', $applicant_id)->where('id', $id)->first();

        $profileData = $this->getProfile($applicant_id);

        $empData = DB::table('employments_applications')->where('applicant_id', $applicant_id)->first();

        $verification_data = [];

        $adminData =  $this->userProfileService->getUser();

        $adminAccount = $this->userProfileService->getAdminAccount();

        if($verification)
        {
            $id = $verification->id;
            $applicant_id = $verification->applicant_id;
            $disciplines = $verification->disciplines;
            $licenseNumber = $verification->licenseNumber;
            $expirationDate = $verification->expirationDate;
            $dateVerified = $verification->dateVerified;
            $licenseVerifiedBy = $verification->licenseVerifiedBy;
            $actionOutstanding = $verification->actionOutstanding;
            $comments = $verification->comments;
            $signature = $verification->signature;
            $created_at = $verification->created_at;

            $hire_date = $empData->employee_hire_date;

            $verification_data[] = [
                'id' => $id,
                'applicant_id' => $applicant_id,
                'disciplines' => $disciplines,
                'licenseNumber' => $licenseNumber,
                'expirationDate' => $expirationDate,
                'dateVerified' => $dateVerified,
                'licenseVerifiedBy' => $licenseVerifiedBy,
                'actionOutstanding' => $actionOutstanding,
                'comments' => $comments,
                'signature' => $signature,
                'created_at' => $created_at,

                'hire_date' => $hire_date
            ];
        }

        return view('admin.applicants.forms.verification', 
        [
            'adminAccount' => $adminAccount,
            'id' => $id, 
            'applicant_id' => $applicant_id, 
            'title' => $title, 
            'verification_data' => $verification_data,
            'profileData' => $profileData,
            'adminData' => $adminData
        ]);
    }

    public function viewReporting($applicant_id, $id)
    {
        $title = 'Reporting: Abuse / Neglect / Exploitation | 1staccess Home Care';

        $reporting = DB::table('reporting')->where('applicant_id', $applicant_id)->where('id', $id)->first();

        $reporting_data = [];

        $profileData = $this->getProfile($applicant_id);

        $adminData =  $this->userProfileService->getUser();

        $adminAccount = $this->userProfileService->getAdminAccount();

        if($reporting)
        {
            $id = $reporting->id;
            $applicant_id = $reporting->applicant_id;
            $signature = $reporting->signature;
            $created_at = $reporting->created_at;

            $reporting_data[] = [
                'id' => $id,
                'applicant_id' => $applicant_id,
                'signature' => $signature,
                'created_at' => $created_at,
            ];
        }

        return view('admin.applicants.forms.reporting', 
        [
            'adminAccount' => $adminAccount,
            'id' => $id, 
            'applicant_id' => $applicant_id, 
            'title' => $title, 
            'reporting_data' => $reporting_data,
            'profileData' => $profileData,
            'adminData'=> $adminData
        ]);
    }

    public function viewAttendanceTardiness($applicant_id, $id)
    {
        $title = 'Employee Notification of Policy: Employee Attendance, Tardiness, Absenteeism and Leave | 1staccess Home Care';

        $profileData = $this->getProfile($applicant_id);

        $attendance_tardiness = DB::table('attendance_tardiness')->where('applicant_id', $applicant_id)->where('id', $id)->first();

        $attendance_tardiness_data = [];

        $empData = DB::table('employments_applications')->where('applicant_id', $applicant_id)->first();

        $adminData =  $this->userProfileService->getUser();

        $adminAccount = $this->userProfileService->getAdminAccount();

        if($attendance_tardiness)
        {
            $id = $attendance_tardiness->id;
            $applicant_id = $attendance_tardiness->applicant_id;
            $signature = $attendance_tardiness->signature;
            $created_at = $attendance_tardiness->created_at;
            $hr_signature = $attendance_tardiness->hr_signature;
            $supervisor_signature = $attendance_tardiness->supervisor_signature;

            $hire_date = $empData->employee_hire_date;
            $position = $empData->position;

            $attendance_tardiness_data[] = [
                'id' => $id,
                'applicant_id' => $applicant_id,
                'signature' => $signature,
                'created_at' => $created_at,
                'hr_signature' => $hr_signature,
                'supervisor_signature' => $supervisor_signature,

                'hire_date' => $hire_date,
                'position' => $position
            ];
        }

        return view('admin.applicants.forms.attendance-tardiness', 
        [
            'adminAccount' => $adminAccount,
            'id' => $id, 
            'applicant_id' => $applicant_id, 
            'title' => $title, 
            'profileData' => $profileData,
            'attendance_tardiness_data' => $attendance_tardiness_data,
            'empData' => $empData,
            'adminData' => $adminData
        ]);
    }

    public function viewApplicationForEmployment($applicant_id, $id)
    {
        $profileData = $this->getProfile($applicant_id);

        $user = $this->getUser($applicant_id);

        $adminData =  $this->userProfileService->getUser();

        // get agency managment notes
        $notes = DB::table('agency_notes')->where('applicant_id', $applicant_id)->where('application_form_id', $id)->first();
        $notesData = [];
        if($notes)
        {
            $notesData[] = [
                'agency_managements_notes' => $notes->agency_managements_notes,
                'applicant_id' => $notes->applicant_id,
                'application_form_id' => $notes->application_form_id
            ];
        }

        $title = 'Application For Employment | 1staccess Home Care';

        $employments_applications = DB::table('employments_applications')->where('applicant_id', $applicant_id)->where('id', $id)->first();
        $present_address = DB::table('present_address')->where('applicant_id', $applicant_id)->first();
        $permanent_address = DB::table('permanent_address')->where('applicant_id', $applicant_id)->first();
        $past_employment_info = DB::table('past_employment_info')->where('applicant_id', $applicant_id)->first();
        $academic_trades_business = DB::table('academic_trades_business')->where('applicant_id', $applicant_id)->first();
        $language = DB::table('language')->where('applicant_id', $applicant_id)->first();
        $refrence = DB::table('reference')->where('applicant_id', $applicant_id)->first();
        $emergency = DB::table('emergency')->where('applicant_id', $applicant_id)->first();
        $signature = DB::table('signature')->where('applicant_id', $applicant_id)->first();

        $adminAccount = $this->userProfileService->getAdminAccount();

        $employment_data = [];

        if($employments_applications)
        {
            $SSN = $employments_applications->SSN;
            $furnish_work = $employments_applications->furnish_work;
            $employment_desired = $employments_applications->employment_desired;
            $position = $employments_applications->position;
            $date_start = $employments_applications->date_start;
            $salary = $employments_applications->salary;
            $employed_now = $employments_applications->employed_now;
            $inqure_present_employer = $employments_applications->inqure_present_employer;
            $applied_before = $employments_applications->applied_before;
            $where = $employments_applications->where;
            $when = $employments_applications->when;
            $on_layoff_subject_to_recall = $employments_applications->on_layoff_subject_to_recall;
            $travel_if_required = $employments_applications->travel_if_required;
            $relocate_if_required = $employments_applications->relocate_if_required;
            $overtime_if_required = $employments_applications->overtime_if_required;
            $attendance_requirements_position = $employments_applications->attendance_requirements_position;
            $bonded = $employments_applications->bonded;
            $convicted = $employments_applications->convicted;
            $explain_convicted = $employments_applications->explain_convicted;
            $drivers_license = $employments_applications->drivers_license;
            $drivers_license_state = $employments_applications->drivers_license_state;
            $special_skills_qualifications = $employments_applications->special_skills_qualifications;
            $employee_hire_date = $employments_applications->employee_hire_date;

            $present_addr = $present_address->present_address;
            $present_city = $present_address->present_city;
            $present_state = $present_address->present_state;
            $present_zip = $present_address->present_zip;

            $permanent_addr = $permanent_address->permanent_address;
            $permanent_city = $permanent_address->permanent_city;
            $permanent_state = $permanent_address->permanent_state;
            $permanent_zip = $permanent_address->permanent_zip;

            $edu_current_name_location_school = $academic_trades_business->edu_current_name_location_school;
            $edu_current_number_years = $academic_trades_business->edu_current_number_years;
            $edu_current_did_graduate = $academic_trades_business->edu_current_did_graduate;
            $edu_current_subjects_studied = $academic_trades_business->edu_current_subjects_studied;
            $edu_last_name_location_school = $academic_trades_business->edu_last_name_location_school;
            $edu_last_number_years = $academic_trades_business->edu_last_number_years;
            $edu_last_did_graduate = $academic_trades_business->edu_last_did_graduate;
            $edu_last_subjects_studied = $academic_trades_business->edu_last_subjects_studied;
            $trades_current_name_location_school = $academic_trades_business->trades_current_name_location_school;
            $trades_current_number_years = $academic_trades_business->trades_current_number_years;
            $trades_current_did_graduate = $academic_trades_business->trades_current_did_graduate;
            $trades_current_subjects_studied = $academic_trades_business->trades_current_subjects_studied;
            $trades_last_current_name_location_school = $academic_trades_business->trades_last_current_name_location_school;
            $trades_last_current_number_years = $academic_trades_business->trades_last_current_number_years;
            $trades_last_current_did_graduate = $academic_trades_business->trades_last_current_did_graduate;
            $trades_last_subjects_studied = $academic_trades_business->trades_last_subjects_studied;

            $from_date_1 = $past_employment_info->from_date_1;
            $to_date_1 = $past_employment_info->to_date_1;
            $name_address_employer_1 = $past_employment_info->name_address_employer_1;
            $phone_number_1 = $past_employment_info->phone_number_1;
            $job_1 = $past_employment_info->job_1;
            $reason_leaving_1 = $past_employment_info->reason_leaving_1;
            $salary_1 = $past_employment_info->salary_1;

            $from_date_2 = $past_employment_info->from_date_2;
            $to_date_2 = $past_employment_info->to_date_2;
            $name_address_employer_2 = $past_employment_info->name_address_employer_2;
            $phone_number_2 = $past_employment_info->phone_number_2;
            $job_2 = $past_employment_info->job_2;
            $reason_leaving_2 = $past_employment_info->reason_leaving_2;
            $salary_2 = $past_employment_info->salary_2;

            $from_date_3 = $past_employment_info->from_date_3;
            $to_date_3 = $past_employment_info->to_date_3;
            $name_address_employer_3 = $past_employment_info->name_address_employer_3;
            $phone_number_3 = $past_employment_info->phone_number_3;
            $job_3 = $past_employment_info->job_3;
            $reason_leaving_3 = $past_employment_info->reason_leaving_3;
            $salary_3 = $past_employment_info->salary_3;

            $reference_name_1 = $refrence->reference_name_1;
            $reference_address_1 = $refrence->reference_address_1;
            $reference_phone_1 = $refrence->reference_phone_1;
            $reference_years_acquainted_1 = $refrence->reference_years_acquainted_1;
            $reference_name_2 = $refrence->reference_name_2;
            $reference_address_2 = $refrence->reference_address_2;
            $reference_phone_2 = $refrence->reference_phone_2;
            $reference_years_acquainted_2 = $refrence->reference_years_acquainted_2;
            $reference_name_3 = $refrence->reference_name_3;
            $reference_address_3 = $refrence->reference_address_3;
            $reference_phone_3 = $refrence->reference_phone_3;
            $reference_years_acquainted_3 = $refrence->reference_years_acquainted_3;

            $language_1 = $language->language_1;
            $read_write_1 = $language->read_write_1;
            $read_speak_1 = $language->read_speak_1;
            $speak_only_1 = $language->speak_only_1;
            $language_2 = $language->language_2;
            $read_write_2 = $language->read_write_2;
            $read_speak_2 = $language->read_speak_2;
            $speak_only_2 = $language->speak_only_2;

            $emergency_address = $emergency->emergency_address;
            $emergency_city = $emergency->emergency_city;
            $emergency_state = $emergency->emergency_state;
            $emergency_zip = $emergency->emergency_zip;

            $sign = $signature->signature;
            $date_signed = $signature->created_at;

            $employment_data[] = [

                'signature' => $sign,
                'date_signed' => $date_signed,

                'emergency_address' => $emergency_address,
                'emergency_city' => $emergency_city,
                'emergency_state' => $emergency_state,
                'emergency_zip' => $emergency_zip,

                'language_1' => $language_1,
                'read_write_1' => $read_write_1,
                'read_speak_1' => $read_speak_1,
                'speak_only_1' => $speak_only_1,
                'language_2' => $language_2,
                'read_write_2' => $read_write_2,
                'read_speak_2' => $read_speak_2,
                'speak_only_2' => $speak_only_2,

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
                'reference_years_acquainted_3' => $reference_years_acquainted_3,

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
                'salary_3' => $salary_3,

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

                'permanent_address' => $permanent_addr,
                'permanent_city' => $permanent_city,
                'permanent_state' => $permanent_state,
                'permanent_zip' => $permanent_zip,

                'present_address' => $present_addr,
                'present_city' => $present_city,
                'present_state' => $present_state,
                'present_zip' => $present_zip,

                'SSN' => $SSN,
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
                'special_skills_qualifications' => $special_skills_qualifications,
                'employee_hire_date' => $employee_hire_date,
            ];
        }

        return view('admin.applicants.forms.employment-application', 
        [
            'adminAccount' => $adminAccount,
            'employment_data' => $employment_data, 
            'id' => $id, 
            'applicant_id' => $applicant_id, 
            'title' => $title,
            'profileData' => $profileData,
            'user' => $user,
            'adminData' => $adminData,
            'notesData' => $notesData,
        ]);

    }

    public function viewPoliciesProcedures($applicant_id, $id)
    {
        $title = 'Policies and Procedures: Orientation Acknowledgement';

        $profileData = $this->getProfile($applicant_id);

        $adminData =  $this->userProfileService->getUser();

        $policies_procedures = DB::table('policy_and_procedure')->where('applicant_id', $applicant_id)->where('id', $id)->first();

        $policies_procedures_data = [];

        $adminAccount = $this->userProfileService->getAdminAccount();

        if($policies_procedures)
        {
            $id = $policies_procedures->id;
            $applicant_id = $policies_procedures->applicant_id;
            $signature = $policies_procedures->signature;
            $created_at = $policies_procedures->created_at;
            $rep_signature = $policies_procedures->rep_signature;

            $policies_procedures_data[] = [
                'id' => $id,
                'applicant_id' => $applicant_id,
                'signature' => $signature,
                'created_at' => $created_at,
                'rep_signature' => $rep_signature
            ];
        }

        return view('admin.applicants.forms.policies-and-procedures', 
        [
            'adminAccount' => $adminAccount,
            'id' => $id, 
            'applicant_id' => $applicant_id, 
            'title' => $title, 
            'policies_procedures_data' => $policies_procedures_data,
            'profileData' => $profileData,
            'adminData' => $adminData
        ]);
    }

    
    
}
