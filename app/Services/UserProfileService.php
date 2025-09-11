<?php

namespace App\Services;

use App\Models\Profile;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserProfileService
{
    public function getAdminAccount()
    {
        $adminAccount = DB::table('admin_profile_migration')->first();

        if (!empty($adminAccount))

            return [
                'admin_fullname' => $adminAccount->admin_fullname,
                'admin_phone' => $adminAccount->admin_phone,
                'admin_avatar' => $adminAccount->admin_avatar,
                'admin_address' => $adminAccount->admin_address
            ];
    }

    public function getUser()
    {
        $userID = Auth::id();
        $user = DB::table('users')->where('id', $userID)->first();

        return [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $user->password,
        ];
    }

    public function getUserProfileData()
    {
        $userID = Auth::id();
        try {
            $profileData = Profile::where('applicant_id', $userID)->first();
            return $profileData;
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
        }
    }
}
