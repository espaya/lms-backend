<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'unique:users'],
            'role' => ['required', 'in:USER'],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
            ],
            'confirm_password' => ['required', 'same:password'],
            'privacy' => ['required', 'in:1'],
            'email' => ['required', 'email', 'unique:users']
        ], [
            'name.required' => 'This field is required',
            'name.unique' => 'You cannot use this username',
            'role.required' => 'This field is required',
            'role.in' => 'Invalid role type',
            'password.required' => 'This field is required',
            'password.string' => 'Invalid inputs',
            'password.min' => 'Input is too short',
            'passowrd.regex' => 'Password must contain at least: 1 uppercase, 1 lowercase, 1 number, and 1 special character',
            'confirm_password.required' => 'This field is required',
            'confirm_password.same' => 'Passwords do not match',
            'privacy.required' => 'Accept our privacy policy',
            'privacy.in' => 'Unknown privacy input',
            'email.required' => 'This field is required',
            'email.email' => 'Invalid input',
            'email.unique' => 'You cannot use this email',
        ]);

        DB::beginTransaction();

        try {
            User::create([
                'name' => trim($request->name),
                'role' => trim($request->role),
                'password' => Hash::make($request->password),
                'privacy' => trim($request->privacy),
                'email' => trim($request->email)
            ]);

            DB::commit();

            return response()->json(['message' => 'User added successfully'], 200);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());
            return response()->json(['message' => 'Error adding user, try again later'], 500);
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
