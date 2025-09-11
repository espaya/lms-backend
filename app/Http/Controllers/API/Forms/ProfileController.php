<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use App\Services\UserProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Input\Input;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    protected $userProfileService;

    public function __construct(UserProfileService $userProfileService)
    {
        $this->userProfileService = $userProfileService;
    }

    public function index(UserProfileService $userProfileService)
    {
        $userID = Auth::id();

        $profileData = $userProfileService->getUserProfileData();

        // position
        $position = DB::table('employments_applications')->where('applicant_id', $userID)->get();
        $positionData = [];
        foreach($position as $item){
            $applicant_id = $item->applicant_id;
            $position = $item->position;
            $positionData[] = [
                'applicant_id' => $applicant_id,
                'position' => $position
            ];
        }

        // permanent address
        $permanentAddress = DB::table('permanent_address')->where('applicant_id', $userID)->get();
        $permanentAddressData = [];
        foreach($permanentAddress as $item){
            $applicant_id = $item->applicant_id;
            $permanent_address = $item->permanent_address;
            $permanent_city = $item->permanent_city;
            $permanent_state = $item->permanent_state;
            $permanent_zip = $item->permanent_zip;
            $permanentAddressData[] = [
                'applicant_id' => $applicant_id,
                'permanent_address' => $permanent_address,
                'permanent_city' => $permanent_city,
                'permanent_state' => $permanent_state,
                'permanent_zip' => $permanent_zip,
            ];
        }

        // $/hr and working days
        $empAgreement = DB::table('employee_agreement')->where('applicant_id', $userID)->get();

        $empAgreementData = [];
        $working_days = 0;
        foreach($empAgreement as $item){
            $applicant_id = $item->applicant_id;
            $pay_per_hour = $item->pay_per_hour;
            $monday_hour = $item->monday_hour;
            $tuesday_hour = $item->tuesday_hour;
            $wednesday_hour = $item->wednesday_hour;
            $thursday_hour = $item->thursday_hour;
            $friday_hour = $item->friday_hour;
            $saturday_hour = $item->saturday_hour;
            $sunday_hour = $item->sunday_hour;

            if(!empty($monday_hour)){
                $working_days += 1;
            }

            if(!empty($tuesday_hour)){
                $working_days += 1;
            }

            if(!empty($wednesday_hour)){
                $working_days += 1;
            }

            if(!empty($thursday_hour)){
                $working_days += 1;
            }

            if(!empty($friday_hour)){
                $working_days += 1;
            }

            if(!empty($saturday_hour)){
                $working_days += 1;
            }

            if(!empty($sunday_hour)){
                $working_days += 1;
            }

            $empAgreementData[] = [
                'applicant_id' => $applicant_id,
                'pay_per_hour' => $pay_per_hour,
                'monday_hour' => $monday_hour,
                'tuesday_hour' => $tuesday_hour,
                'wednesday_hour' => $wednesday_hour,
                'thursday_hour' => $thursday_hour,
                'friday_hour' => $friday_hour,
                'saturday_hour' => $saturday_hour,
                'sunday_hour' => $sunday_hour,
                'working_days' => $working_days
            ];
        }

        $delete = DB::table('request_delete')->where('applicant_id', $userID)->get();
        $deleteData = [];
        foreach($delete as $item){
            $applicant_id = $item->applicant_id;
            $deleteData[] = [
                'applicant_id' => $applicant_id
            ];
        }

        // recent update record
        // $recentRecord = $this->getRecentlyUpdatedRecord();

        return view('dashboard.profile.index', 
            [
                'profileData' =>  $profileData, 
                'positionData' => $positionData, 
                'permanentAddressData' => $permanentAddressData, 
                'empAgreementData' => $empAgreementData,
                'deleteData' => $deleteData
                // 'recentRecord' => $recentRecord
            ]);
    }


public function update(Request $request, $id)
{
    $id = Auth::id();

    $validator = Validator::make($request->all(), [
        'current_password' => 'required',
        'new_password' => [
            'required',
            'min:10',
            'regex:/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]+$/',
        ],
        'confirm_password' => 'required|same:new_password',
    ]);

    $validator->after(function ($validator) use ($request) {
        $authID = Auth::id();
        $user = DB::table('users')->where('id', $authID)->first();
    
        if (Hash::check($request->input('current_password'), $user->password)) {
            $validator->errors()->add('current_password', 'The current password is incorrect.');
        }
    });

    if ($validator->fails()) {
        throw new ValidationException($validator);
    }

    $newPassword = $request->input('new_password');
    $user = User::find($id);
    $user->password = Hash::make($newPassword);
    $user->save();

    return redirect()->route('profile.index')->with(['user' => $user, 'success' => 'Password Updated Successfully']); //Handsomeboygirl1@ Magicmediaghana77@
}

    public function edit($id)
    {
        $user = User::find($id);
        return view('dashboard.profile.index', compact('user'));
    }

    public function store(Request $request)
    {
       

    }

    
    // public function getRecentlyUpdatedRecord()
    // {
    //     $userID = auth()->id();
    //     $recentRecord = null;
    //     $recentTable = null;

    //     $tables = ['applicant_files', 'confidentiality', 'criminal_history_search', 'disclaimer_waiver_liability', 'drug_testing_policy', 'employee_agreement', 'employee_conduct', 'employee_dress_code', 'employee_orientation', 'employee_reference_check', 'employee_safety', 'health_safety_agreement', 'home_health_aide', 'infection_control_agreement', 'non_compete_agreement', 'policy_and_procedure', 'reporting', 'sexual_harassment', 'smoking_in_the_workplace', 'sworn_disclosure_statement', 'universal_precaution', 'verification', 'signature'];

    //     foreach($tables as $table){
    //         if(Schema::hasTable($table)){
    //             $record = DB::table($table)->where('applicant_id', $userID)->orderBy('updated_at', 'desc')->first();
    //             if($record && (!$recentRecord || $record->updated_at > $recentRecord->updated_at)){
    //                 $recentRecord = $record;
    //                 $recentTable = $table;
    //             }
    //         }
    //     }

    //     return ['table' => $recentTable, 'record' => $recentRecord];

    // }


}
