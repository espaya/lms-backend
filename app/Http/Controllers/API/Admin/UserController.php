<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Mail\UserAccountEmail;
use App\Models\AcademicTrades;
use App\Models\AgencyManagementNotes;
use App\Models\Answer;
use App\Models\AttendanceTardiness;
use App\Models\ConfidentialityInformation;
use App\Models\CriminalHistorySearch;
use App\Models\DisclaimerWaiverLiability;
use App\Models\DrugTestingPolicy;
use App\Models\Emergency;
use App\Models\EmployeeAgreement;
use App\Models\EmployeeConduct;
use App\Models\EmployeeDressCode;
use App\Models\EmployeeOrientation;
use App\Models\EmployeeReferenceCheck;
use App\Models\EmployeeSafety;
use App\Models\EmploymentApplication;
use App\Models\HealthSafetyAgreement;
use App\Models\HomeHealthAide;
use App\Models\InfectionControlAgreement;
use App\Models\Language;
use App\Models\NonCompeteAgreement;
use App\Models\PastEmpInfo;
use App\Models\PolicyAndProcedure;
use App\Models\PremanentAddress;
use App\Models\PresentAdrress;
use App\Models\Profile;
use App\Models\Reference;
use App\Models\Reporting;
use App\Models\RepSignature;
use App\Models\SexualHarassment;
use App\Models\Signature;
use App\Models\Smoking;
use App\Models\SwornDisclosure;
use App\Models\UniversalPrecautions;
use App\Models\User;
use App\Models\Verification;
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

    public function destroy($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json(['message' => 'User not found!'], 404);
            }

            $academicTrades = AcademicTrades::where('applicant_id', $id)->first();

            if ($academicTrades) {
                $academicTrades->delete();
            }

            $attendanceTardiness = AttendanceTardiness::where('applicant_id', $id)->first();

            if ($attendanceTardiness) {
                //delete signature 
                if (!empty($attendanceTardiness->signature)) {
                    $signaturePath = storage_path('public/signature/' . $attendanceTardiness->signature);
                    if (file_exists($signaturePath)) {
                        unlink($signaturePath);
                    }
                }

                $attendanceTardiness->delete();
            }

            $confidentiality = ConfidentialityInformation::where('applicant_id', $id)->first();

            if ($confidentiality) {
                if (!empty($confidentiality->signature)) {
                    $signaturePath = storage_path('public/signature/' . $confidentiality->signature);
                    if (file_exists($signaturePath)) {
                        unlink($signaturePath);
                    }
                }

                $confidentiality->delete();
            }

            $criminal = CriminalHistorySearch::where('applicant_id', $id)->first();

            if ($criminal) {
                if (!empty($criminal->signature)) {
                    $signaturePath = storage_path('public/signature/' . $criminal->signature);
                    if (file_exists($criminal->signature)) {
                        unlink($signaturePath);
                    }
                }

                $criminal->delete();
            }

            $disclaimer = DisclaimerWaiverLiability::where('applicant_id', $id)->first();

            if ($disclaimer) {
                if (!empty($disclaimer->signature)) {
                    $signaturePath = storage_path('public/signature/' . $disclaimer->signature);
                    if (file_exists($disclaimer->signature)) {
                        unlink($signaturePath);
                    }
                }
                $disclaimer->delete();
            }

            $drugTesting = DrugTestingPolicy::where('applicant_id', $id)->first();

            if ($drugTesting) {
                if (!empty($drugTesting->signature)) {
                    $signaturePath = storage_path('public/signature/' . $drugTesting->signature);
                    if (file_exists($drugTesting->signature)) {
                        unlink($signaturePath);
                    }
                }

                $drugTesting->delete();
            }

            $emergency = Emergency::where('applicant_id', $id)->first();

            if ($emergency) {
                $emergency->delete();
            }

            $employeeAgreement = EmployeeAgreement::where('applicant_id', $id)->first();

            if ($employeeAgreement) {
                if (!empty($employeeAgreement->signature)) {
                    $signaturePath = storage_path('public/signature/' . $employeeAgreement->signature);
                    if (file_exists($employeeAgreement->signature)) {
                        unlink($signaturePath);
                    }
                }

                $employeeAgreement->delete();
            }


            $employeeConduct = EmployeeConduct::where('applicant_id', $id)->first();

            if ($employeeConduct) {
                if (!empty($employeeConduct->signature)) {
                    $signaturePath = storage_path('public/signature/' . $employeeConduct->signature);
                    if (file_exists($employeeConduct->signature)) {
                        unlink($signaturePath);
                    }
                }
                $employeeConduct->delete();
            }

            $employeeDressCode = EmployeeDressCode::where('applicant_id', $id)->first();

            if ($employeeDressCode) {
                if (!empty($employeeDressCode->signature)) {
                    $signaturePath = storage_path('public/signature/' . $employeeDressCode->signature);
                    if (file_exists($employeeDressCode->signature)) {
                        unlink($signaturePath);
                    }
                }
                $employeeDressCode->delete();
            }

            $employeeOrientation = EmployeeOrientation::where('applicant_id', $id)->first();

            if ($employeeOrientation) {
                if (!empty($employeeOrientation->signature)) {
                    $signaturePath = storage_path('public/signature/' . $employeeOrientation->signature);
                    if (file_exists($employeeOrientation->signature)) {
                        unlink($signaturePath);
                    }
                }
                $employeeOrientation->delete();
            }

            $employeeReferenceCheck = EmployeeReferenceCheck::where('applicant_id', $id)->first();

            if ($employeeReferenceCheck) {
                if (!empty($employeeReferenceCheck->signature)) {
                    $signaturePath = storage_path('public/signature/' . $employeeReferenceCheck->signature);
                    if (file_exists($employeeReferenceCheck->signature)) {
                        unlink($signaturePath);
                    }
                }
                $employeeReferenceCheck->delete();
            }

            $employeeSafety = EmployeeSafety::where('applicant_id', $id)->first();

            if ($employeeSafety) {
                if (!empty($employeeSafety->signature)) {
                    $signaturePath = storage_path('public/storage/' . $employeeSafety->signature);
                    if (file_exists($employeeSafety->signature)) {
                        unlink($signaturePath);
                    }
                }
                $employeeSafety->delete();
            }

            $employmentApplication = EmploymentApplication::where('applicant_id', $id)->first();

            if ($employmentApplication) {
                $employmentApplication->delete();
            }

            $healthSafetyAgreement = HealthSafetyAgreement::where('applicant_id', $id)->first();

            if ($healthSafetyAgreement) {
                if (!empty($healthSafetyAgreement->signature)) {
                    $signaturePath = storage_path('public/signature/' . $healthSafetyAgreement->signature);
                    if (file_exists($healthSafetyAgreement->signature)) {
                        unlink($signaturePath);
                    }
                }
                $healthSafetyAgreement->delete();
            }

            $homeHealthAide = HomeHealthAide::where('applicant_id', $id)->first();

            if ($homeHealthAide) {
                if (!empty($homeHealthAide->signature)) {
                    $signaturePath = storage_path('public/signature/' . $homeHealthAide->signature);
                    if (file_exists($homeHealthAide->signature)) {
                        unlink($signaturePath);
                    }
                }
                $homeHealthAide->delete();
            }

            $infectionControlAgreement = InfectionControlAgreement::where('applicant_id', $id)->first();

            if ($infectionControlAgreement) {
                if (!empty($infectionControlAgreement->signature)) {
                    $signaturePath = storage_path('public/signature/' . $infectionControlAgreement->signature);
                    if (file_exists($infectionControlAgreement->signature)) {
                        unlink($signaturePath);
                    }
                }
                $infectionControlAgreement->delete();
            }

            $language = Language::where('applicant_id', $id)->first();

            if ($language) {
                $language->delete();
            }

            $nonCompeteAgreement = NonCompeteAgreement::where('applicant_id', $id)->first();

            if ($nonCompeteAgreement) {

                if (!empty($nonCompeteAgreement->signature)) {
                    $signaturePath = storage_path('public/signature/' . $nonCompeteAgreement->signature);
                    if (file_exists($nonCompeteAgreement->signature)) {
                        unlink($signaturePath);
                    }
                }

                if (!empty($nonCompeteAgreement->agency_rep_signature)) {
                    $path = storage_path('public/signature/' . $nonCompeteAgreement->signature);
                    if (file_exists($nonCompeteAgreement->agency_rep_signature)) {
                        unlink($path);
                    }
                }

                $nonCompeteAgreement->delete();
            }

            $pastEmpInfo = PastEmpInfo::where('applicant_id', $id)->first();

            if ($pastEmpInfo) {
                $pastEmpInfo->delete();
            }

            $policyAndProcedure = PolicyAndProcedure::where('applicant_id', $id)->first();

            if ($policyAndProcedure) {
                if (!empty($policyAndProcedure->signature)) {
                    $signaturePath = storage_path('public/signature/' . $policyAndProcedure->signature);
                    if (file_exists($policyAndProcedure->signature)) {
                        unlink($signaturePath);
                    }
                }
                $policyAndProcedure->delete();
            }

            $permanentAddress = PremanentAddress::where('applicant_id', $id)->first();

            if ($permanentAddress) {
                $permanentAddress->delete();
            }

            $presentAddress = PresentAdrress::where('applicant_id', $id)->first();

            if ($presentAddress) {
                $presentAddress->delete();
            }

            $profile = Profile::where('applicant_id', $id)->first();

            if ($profile) {
                $profile->delete();
            }

            $reference = Reference::where('applicant_id', $id)->first();

            if ($reference) {
                $reference->delete();
            }

            $reporting = Reporting::where('applicant_id', $id)->first();

            if ($reporting) {
                if (!empty($reporting->signature)) {
                    $signaturePath = storage_path('public/signature/' . $reporting->signature);
                    if (file_exists($reporting->signature)) {
                        unlink($signaturePath);
                    }
                }
                $reporting->delete();
            }

            // $repSignature = RepSignature::where('applicant_id', $id)->first();

            // if ($repSignature) {
            //     if (!empty($repSignature->rep_signature)) {
            //         $signaturePath = storage_path('public/signature/' . $repSignature->rep_signature);
            //         if (file_exists($repSignature->rep_signature)) {
            //             unlink($signaturePath);
            //         }
            //     }
            //     $repSignature->delete();
            // }

            $sexualHarassment = SexualHarassment::where('applicant_id', $id)->first();

            if ($sexualHarassment) {
                if (!empty($sexualHarassment->signature)) {
                    $signaturePath = storage_path('public/signature/' . $sexualHarassment->signature);
                    if (file_exists($sexualHarassment->signature)) {
                        unlink($signaturePath);
                    }
                }
                $sexualHarassment->delete();
            }

            $signature = Signature::where('applicant_id', $id)->first();

            if ($signature) {
                if (!empty($signature->signature)) {
                    $signaturePath = storage_path('public/signature/' . $signature->signature);
                    if (file_exists($signature->signature)) {
                        unlink($signaturePath);
                    }
                }
                $signature->delete();
            }

            $smoking = Smoking::where('applicant_id', $id)->first();

            if ($smoking) {
                if (!empty($smoking->signature)) {
                    $signaturePath = storage_path('public/signature/' . $smoking->signature);
                    if (file_exists($smoking->signature)) {
                        unlink($signaturePath);
                    }
                }

                if (!empty($smoking->supervisor_signature)) {
                    $supSignaturePath = storage_path('public/signature/' . $smoking->supervisor_signature);
                    if (file_exists($smoking->supervisor_signature)) {
                        unlink($supSignaturePath);
                    }
                }

                if (!empty($smoking->hr_signature)) {
                    $hrSignaturePath = storage_path('public/signature/' . $smoking->hr_signature);
                    if (file_exists($smoking->hr_signature)) {
                        unlink($hrSignaturePath);
                    }
                }

                $smoking->delete();
            }

            $swornDisclosure = SwornDisclosure::where('applicant_id', $id)->first();

            if ($swornDisclosure) {
                if (!empty($swornDisclosure->signature)) {
                    $signaturePath = storage_path('public/signature/' . $swornDisclosure->signature);
                    if (file_exists($swornDisclosure->signature)) {
                        unlink($signaturePath);
                    }
                }

                if (!empty($swornDisclosure->wit_signature)) {
                    $witSignaturePath = storage_path('public/signature/' . $swornDisclosure->wit_signature);
                    if (file_exists($swornDisclosure->wit_signature)) {
                        unlink($witSignaturePath);
                    }
                }

                $swornDisclosure->delete();
            }

            $universalPrecautions = UniversalPrecautions::where('applicant_id', $id)->first();

            if ($universalPrecautions) {
                if (!empty($universalPrecautions->signature)) {
                    $signaturePath = storage_path('public/signature/' . $universalPrecautions->signature);
                    if (file_exists($universalPrecautions->signature)) {
                        unlink($signaturePath);
                    }
                }
                $universalPrecautions->delete();
            }

            $verification = Verification::where('applicant_id', $id)->first();

            if ($verification) {
                if (!empty($verification->signature)) {
                    $signaturePath = storage_path('public/signature/' . $verification->signature);
                    if (file_exists($verification->signature)) {
                        unlink($signaturePath);
                    }
                }
                $verification->delete();
            }

            $agencyMgtNotes = AgencyManagementNotes::where('applicant_id', $id)->get();

            if ($agencyMgtNotes->isNotEmpty()) {
                foreach ($agencyMgtNotes as $note) {
                    $note->delete();
                }
            }


            $answers = Answer::where('user_id', $id)->get();

            if ($answers->isNotEmpty()) {
                foreach ($answers as $answer) {
                    if (!empty($answer->signature)) {
                        $signaturePath = storage_path('app/public/signature/' . $answer->signature);

                        if (file_exists($signaturePath)) {
                            unlink($signaturePath);
                        }
                    }

                    $answer->delete();
                }
            }

            $user->delete();

            return response()->json(['message' => 'User deleted successfully!'], 200);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json(['message' => 'An unexpected error occurred'], 500);
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
