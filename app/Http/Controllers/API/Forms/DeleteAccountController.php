<?php

namespace App\Http\Controllers;

use App\Models\Delete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DeleteAccountController extends Controller
{
    public function index()
    {
        
    }

    public function store(Request $request)
    {
        $userID = Auth::id();

        $request->validate([
            'your_password' => 'required'
        ]);

        $delete = new Delete();

        $currentPassword = $request->input('your_password');

        if(!Hash::check($currentPassword, Auth::user()->password)){
            return redirect()->back()->withErrors('your_password', 'The password do not match');
        }

        Delete::firstOrCreate(
            ['applicant_id' => $userID],
            ['applicant_id' => $userID]
        );

        return redirect()->back()->with('request_delete', 'Thank you for requesting account delete. The HR team will review your request');
    }
}
