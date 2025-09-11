<?php

namespace App\Http\Controllers;

use App\Models\AdminProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AdminProfileService;
use App\Services\UserProfileService;
use App\Services\UserStatisticsService;
use Dotenv\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

use function PHPUnit\Framework\fileExists;

class AdminController extends Controller
{
    protected $userProfileService, $userStatisticsService;

    public function __construct(UserProfileService $userProfileService, UserStatisticsService $userStatisticsService)
    {
        $this->userProfileService = $userProfileService;
        $this->userStatisticsService = $userStatisticsService;

    }

    public function index()
    {
        $userID = Auth::id();

        $title = 'Admin  Dashbaord | 1st Access Home Care';

        $adminData =  $this->userProfileService->getUser();

        $adminAccount =  $this->userProfileService->getAdminAccount();

        // count applicants 
        $userCount = DB::table('users')->where('role', 'USER')->count();

        $userPercentage = $this->userStatisticsService->getUserCounts();

        $signedApplications = $this->userStatisticsService->countApplications();

        $countUnsignedApplications = $this->userStatisticsService->countUnsignedApplications();
        
        return view('/admin.home', ['userID' => $userID, 'title' => $title, 'adminData' => $adminData, 'adminAccount' => $adminAccount, 'userCount' => $userCount, 'userPercentage' => $userPercentage, 'signedApplications' => $signedApplications, 'countUnsignedApplications' => $countUnsignedApplications]);
    }

    public function searchApplicants(Request $request)
    {
        $title = 'Search Results - Applicants | 1staccess Home Care ';

        $adminData =  $this->userProfileService->getUser();

        // validate the search field 
        $request->validate([
            'search' => ['required', 'string', 'min:3']
        ]);

        // retrieve search query from the request
        $searchQuery = $request->input('search');

        // Perform the search logic using the $searchQuery and retrieve the search results
        $searchResults = DB::table('users_profile')->where('full_name', 'like', '%' . $searchQuery . '%')->get();

        // get admin info
        $adminAccount = $this->userProfileService->getAdminAccount();

        return view('admin.applicants.index', 
        [
            'searchResults' => $searchResults, 
            'title' => $title, 
            'adminData' => $adminData, 
            'adminAccount' => $adminAccount
        ]);
    }

    public function show($id)
    {
        return view('admin.profile.index');
    }

    public function showProfilePage()
    {
        $adminData =  $this->userProfileService->getUser();

        $adminAccount = $this->userProfileService->getAdminAccount();

        $title = $adminData['name'] . " Profile | 1staccess Home Care";

        return view('admin.profile.index', ['adminData' => $adminData, 'title' => $title, 'adminAccount' => $adminAccount]);   
    }

    public function updateAccount(Request $request)
    {
        $admin_ID = Auth::id();

        $request->validate([
            'username' => ['required', 'string', 'min:5', 'regex:/^[a-zA-Z0-9]+$/', Rule::unique('users')->ignore($admin_ID)],
            'email' => ['required', 'string', 'type:email', Rule::unique('users')->ignore($admin_ID)],
            'current_password' => ['required', 'password'],
            'new_password' => ['required', 'regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'],
            'confirm_password' => ['required', 'same:new_password'],
        ]);

        DB::table('users')->where('id', $admin_ID)->update([
            'name' => $request->input('username'),
            'email' => $request->input('email'),
            'password' => hash::make($request->input('new_password')),
            'updated-at' => now(),
        ]);

        return redirect()->back()->with('success', 'Account Updated Successfully');
    }

    public function updateAvatar(Request $request)
    {
        $admin_ID = Auth::id();

        $request->validate([
            'avatar' => ['required', 'mimes:jpg,jpeg,png,webp', 'max:5000'],
        ]);

        // check if admin table is empty, insert data else update
        $check = DB::table('admin_profile_migration')->where('admin_ID', $admin_ID)->first();

        if(empty($check))
        {
            // insert name of image into db and save file in the directory
            $uploadedFile = $request->file('avatar');
            $avatarName = time() . '.' . $uploadedFile->extension();
            $storedPath = $uploadedFile->storeAs('avatars', $avatarName, 'public');

            // insert filename into the db
            DB::table('admin_profile_migration')->insert([
                'admin_ID' => $admin_ID,
                'admin_avatar' => $avatarName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        else
        {
            // get old avatar name from the db
            $oldAvatar = $check->admin_avatar;

            // update the db with the filename and save the file in the directory
            $uploadedFile = $request->file('avatar');
            $avatarName = time() . '.' . $uploadedFile->extension();
            $storedPath = $uploadedFile->storeAs('avatars', $avatarName, 'public');

            // update filename in the db
            DB::table('admin_profile_migration')->where('admin_ID', $admin_ID)
            ->update([
                'admin_avatar' => $avatarName,
                'updated_at' => now(),
            ]);

            // delete old avatar from directory
            if($oldAvatar)
            {
                $oldAvatarPath = public_path('avatars') . '/' . $oldAvatar;
                if(fileExists($oldAvatarPath))
                {
                    unlink($oldAvatarPath);
                }
            }
        }

        return redirect()->back()->with('success', 'Profile Picture Updated Successfully');
    }

    public function updateProfile(Request $request)
    {
        $admin_ID = Auth::id();

        $request->validate([
            'full_name' => 'required|string',
            'phone' => ['required', 'string', 'regex:/^[0-9+]+$/', 'min:10'],
            'address' => 'required|string'
        ]);

        // check if admin table is empty, insert data else update
        $check = DB::table('admin_profile_migration')->where('admin_ID', $admin_ID)->first();

        if(empty($check))
        {
            // if empty insert data
            DB::table('admin_profile_migration')->insert([
                'admin_fullname' => $request->input('full_name'),
                'admin_phone' => $request->input('phone'),
                'admin_address' => $request->input('address'),
                'admin_ID' => $admin_ID,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        else
        {
            // if not empty update data
            DB::table('admin_profile_migration')->where('admin_ID', $admin_ID)
            ->update([
                'admin_fullname' => $request->input('full_name'),
                'admin_phone' => $request->input('phone'),
                'admin_address' => $request->input('address'),
                'admin_ID' => $admin_ID,
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('profile')->with('success', 'Profile Updated Successfully');
    }

    public function create()
    {

    }

    public function store(Request $request)
    {

    }

    public function edit($id){

    }

    public function update(Request $request, $id)
    {

    }

    public function destroy($id)
    {

    }
}
