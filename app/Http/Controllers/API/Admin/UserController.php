<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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
}
