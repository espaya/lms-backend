<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AvatarController extends Controller
{
    public function index()
    {
        $userID = Auth::id();

        $userID = Auth::id();
        // full name
        $profile =DB::table('users_profile')->where('applicant_id', $userID)->get();
        $profileData = [];
        foreach($profile as $item){
            $applicant_id = $item->applicant_id;
            $full_name = $item->full_name;
            $user_avatar = $item->user_avatar;
            $profileData[] = [
                'applicant_id' => $applicant_id,
                'full_name' => $full_name,
                'user_avatar' => $user_avatar
            ];
        }

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

        return view('dashboard.profile.profile-avatar', [
            'profileData' =>  $profileData, 
            'positionData' => $positionData, 
            'permanentAddressData' => $permanentAddressData, 
            'empAgreementData' => $empAgreementData,
        ]);
    }


    public function edit($id)
    {
        $user = User::find($id);
        dd($user);
        return view('dashboard.profile.profile-avatar', compact('user'));
    }
    

    public function update(Request $request, $id)
    {
        $id = Auth::id();

        $request->validate([
            'avatar' => 'required|image|mimes:jpg,png,jpeg,webp,gif|max:2048'
        ]);

        $profileData = new Profile();
        $profileID = Profile::find($id);
        $profileData->user_avatar = $request->avatar;

        $file = $request->avatar;

        $fileName = time() . '.' . $file->getClientOriginalExtension();

        $file->storeAs('public/avatars', $fileName);

        $userID = Auth::id();

        $users_profile = Profile::where('applicant_id', $userID)->first();

        // update user_avatar column
        if($users_profile){
            // delete the existing profile picture from the directory
            Storage::delete('public/avatars' . $users_profile->user_avatar);

            $users_profile->user_avatar = $fileName;
            $users_profile->save();
        }

        return redirect()->route('profile.index')->with(['user' => $userID, 'success' => 'Avatar Updated Successfully']);
    }


    


    public function store(Request $request)
    {

    }

}
