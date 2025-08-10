<?php

namespace App\Http\Controllers\API\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index($username)
    {
        try {
            $user = User::where('name', $username)->first();

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            return response()->json($user);
        } catch (Exception $ex) {
            Log::error('Error fetching user data: ' . $ex->getMessage());
            return response()->json(['message' => 'Error getting user data'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                Rule::unique('users', 'name')->ignore($id) // unique but ignore current user
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($id) // same logic for email
            ],
            'old_password' => ['nullable', 'string', function ($attribute, $value, $fail) {
                if (!Hash::check($value, Auth::user()->password)) {
                    $fail('The old password is incorrect.');
                }
            }],
            'new_password' => [
                'nullable',
                'string',
                // 'confirmed', // requires confirm_password field
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
                'different:old_password'
            ],
            'confirm_password' => ['same:new_password']
        ], [
            'new_password.regex' => 'Password must be at least 8 characters long, contain uppercase, lowercase, number, and special character.'
        ]);

        $user = Auth::user();

        try {

            if ($user->id != $id) {
                return response()->json(['message' => 'Your account was not found!'], 404);
            }

            // Update fields
            $user->name = $request->name;
            $user->email = $request->email;

            // Only hash and set password if a new one is provided
            if ($request->filled('new_password')) {
                $user->password = Hash::make($request->new_password);
            }

            // Save only if changes are made
            if ($user->isDirty()) {
                $user->save();
                return response()->json(['message' => 'User updated successfully']);
            }

            return response()->json(['message' => 'No changes detected']);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json(['message' => 'Error occurred whilst saving your details']);
        }
    }
}
