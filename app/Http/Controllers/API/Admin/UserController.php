<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
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
