<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Mail\UserAccountEmail;
use App\Models\Answer;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function store(Request $request)
    {
        // Validate each user inside the 'users' array
        $request->validate([
            'users' => ['required', 'array', 'min:1'],
            'users.*.name' => ['required', 'unique:users,name'],
            'users.*.role' => ['required', 'in:USER'],
            'users.*.password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
            ],
            'users.*.confirm_password' => ['required', 'same:users.*.password'],
            'users.*.privacy' => ['required', 'in:1'],
            'users.*.email' => ['required', 'email', 'unique:users,email']
        ], [
            'users.required' => 'You must provide at least one user.',
            'users.array' => 'Invalid format for users.',

            'users.*.name.required' => 'This field is required',
            'users.*.name.unique' => 'You cannot use this username',

            'users.*.role.required' => 'This field is required',
            'users.*.role.in' => 'Invalid role type',

            'users.*.password.required' => 'This field is required',
            'users.*.password.string' => 'Invalid inputs',
            'users.*.password.min' => 'Input is too short',
            'users.*.password.regex' => 'Password must contain at least: 1 uppercase, 1 lowercase, 1 number, and 1 special character',

            'users.*.confirm_password.required' => 'This field is required',
            'users.*.confirm_password.same' => 'Passwords do not match',

            'users.*.privacy.required' => 'Accept our privacy policy',
            'users.*.privacy.in' => 'Unknown privacy input',

            'users.*.email.required' => 'This field is required',
            'users.*.email.email' => 'Invalid email format',
            'users.*.email.unique' => 'You cannot use this email',
        ]);

        DB::beginTransaction();

        try {
            foreach ($request->users as $userData) {
                $user = User::create([
                    'name' => trim($userData['name']),
                    'role' => trim($userData['role']),
                    'password' => Hash::make($userData['password']),
                    'privacy' => trim($userData['privacy']),
                    'email' => trim($userData['email']),
                ]);

                // email user after creating their account
                Mail::to($user->email)->send(new UserAccountEmail($user));
            }



            DB::commit();

            return response()->json(['message' => 'Users added successfully'], 200);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());
            return response()->json(['message' => 'Error adding users, try again later'], 500);
        }
    }



    public function updateUser(Request $request, $id)
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
            'role' => ['nullable', 'string', 'in:USER'],
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

        try {
            $user = User::findOrFail($id);

            if (!$user) {
                return response()->json(['message' => 'This user was not found!']);
            }

            // Update fields
            $user->name = $request->name;
            $user->email = $request->email;
            // $user->role = $request->role ?? $user->role;

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
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'An error occurred while updating user.',
                'error' => $ex->getMessage()
            ], 500);
        }
    }


    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Default 10 items per page
            $users = User::where('role', "USER")
                ->orderBy("id", "DESC")
                ->paginate($perPage);

            return response()->json([
                'data' => $users->items(),
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ]
            ]);
        } catch (Exception $ex) {
            Log::error("Error fetching users: " . $ex->getMessage());
            return response()->json(
                ['message' => 'Error fetching users, contact your website admin'],
                500
            );
        }
    }

    public function view($username)
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

    public function getUserAnswers(Request $request,  $username)
    {
        try {
            $perPage = $request->input("per_page", 10);
            $user = User::where('name', $username)->first();

            if (!$user) {
                return response()->json(['message' => 'User not found!'], 404);
            }

            $answers = Answer::with('topic')
                ->where('user_id', $user->id)
                ->orderBy("created_at", "DESC")
                ->paginate($perPage);

            if ($answers->isEmpty()) {
                return response()->json(['message' => "This user's report was not found!"], 404);
            }

            return response()->json([
                'user' => $user->only(['id', 'name', 'email']),
                'answers' => $answers->items(),
                'meta' => [
                    'current_page' => $answers->currentPage(),
                    'last_page' => $answers->lastPage(),
                    'per_page' => $answers->perPage(),
                    'total' => $answers->total()
                ]
            ]);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json(['message' => 'Error getting report'], 500);
        }
    }
}
