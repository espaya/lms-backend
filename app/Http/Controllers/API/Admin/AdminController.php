<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * ===============================
     * DASHBOARD SUMMARY (OPTIONAL)
     * ===============================
     */
    public function dashboardSummary()
    {
        return response()->json([
            'users' => $this->users(),
            'quizzes' => $this->quizzes(),
            'forms' => $this->forms(),
        ]);
    }

    /**
     * ===============================
     * TOTAL USERS
     * ===============================
     */
    public function users()
    {
        $total = DB::table('users')->count();

        $active = DB::table('users')
            ->whereNotNull('email_verified_at')
            ->count();

        $inactive = DB::table('users')
            ->whereNull('email_verified_at')
            ->count();

        return response()->json([
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
        ]);
    }

    /**
     * ===============================
     * QUIZ STATS
     * ===============================
     */
    public function quizzes()
    {
        $total = DB::table('quizzes')->count();

        $attempts = DB::table('answers')->count();

        $passed = DB::table('answers')
            ->whereRaw('score >= (total * 0.5)')
            ->count();

        $failed = $attempts - $passed;

        $avgScore = DB::table('answers')
            ->select(DB::raw('AVG(score) as avg'))
            ->value('avg');

        return response()->json([
            'total' => $total,
            'attempts' => $attempts,
            'passed' => $passed,
            'failed' => $failed,
            'average_score' => round($avgScore, 2),
        ]);
    }

    /**
     * ===============================
     * FORMS / COMPLIANCE
     * ===============================
     */
    public function forms()
    {
        $tables = [
            'employee_agreement',
            'confidentiality',
            'disclaimer_waiver_liability',
            'drug_testing_policy',
            'infection_control_agreement',
            'health_safety_agreement',
        ];

        $total = 0;

        foreach ($tables as $table) {
            $total += DB::table($table)->count();
        }

        // Example: assume "status" column exists
        $completed = DB::table('employee_agreement')
            ->whereNotNull('signature')
            ->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => $total - $completed,
        ];
    }

    /**
     * ===============================
     * QUICK ACTIONS
     * ===============================
     */
    public function quickActions()
    {
        return response()->json([
            'totalUsers' => DB::table('users')->count(),

            'newUsersToday' => DB::table('users')
                ->whereDate('created_at', Carbon::today())
                ->count(),

            'pendingVerifications' => DB::table('verification')
                ->whereNotNull('signature')
                ->count(),

            'pendingRequests' => DB::table('request_delete')->count(),

            'reports' => DB::table('reporting')->count(),
        ]);
    }

    /**
     * ===============================
     * RECENT USERS
     * ===============================
     */
    public function recentUsers()
    {
        $users = DB::table('users')
            ->leftJoin('users_profile', 'users.id', '=', 'users_profile.applicant_id')

            // join answers to check signature
            ->leftJoin('answers', 'users.id', '=', 'answers.user_id')

            ->select(
                'users.id',
                'users.email',
                DB::raw('COALESCE(users_profile.full_name, users.name) as name'),
                'users_profile.user_avatar',

                // NEW: signature-based status
                DB::raw('
                CASE 
                    WHEN MAX(answers.signature) IS NOT NULL 
                    THEN "completed" 
                    ELSE "pending" 
                END as status
            '),

                'users.created_at'
            )
            ->groupBy(
                'users.id',
                'users.email',
                'users_profile.full_name',
                'users.name',
                'users_profile.user_avatar',
                'users.created_at'
            )
            ->orderByDesc('users.created_at')
            ->limit(10)
            ->get();

        return response()->json($users);
    }

    /**
     * ===============================
     * TOP USERS (LEADERBOARD)
     * ===============================
     */
    public function topUsers()
    {
        $users = DB::table('users')
            ->leftJoin('users_profile', 'users.id', '=', 'users_profile.applicant_id')

            // join answers (real attempts)
            ->leftJoin('answers', 'users.id', '=', 'answers.user_id')

            ->select(
                'users.id',
                'users.email',
                DB::raw('COALESCE(users_profile.full_name, users.name) as name'),
                'users_profile.user_avatar',

                // total attempts
                DB::raw('COUNT(answers.id) as attempts'),

                // passed (>=50%)
                DB::raw('SUM(CASE 
                WHEN answers.score >= (answers.total * 0.5) 
                THEN 1 ELSE 0 END) as passed'),

                // average score %
                DB::raw('ROUND(AVG(
                CASE 
                    WHEN answers.total > 0 
                    THEN (answers.score / answers.total) * 100 
                    ELSE 0 
                END
            ), 2) as score')
            )
            ->groupBy(
                'users.id',
                'users.email',
                'users_profile.full_name',
                'users.name',
                'users_profile.user_avatar'
            )
            ->orderByDesc('score')
            ->limit(10)
            ->get();

        // attach forms (still separate but small cost)
        $users = $users->map(function ($user) {

            $formsCompleted =
                DB::table('employee_agreement')->where('applicant_id', $user->id)->count() +
                DB::table('confidentiality')->where('applicant_id', $user->id)->count() +
                DB::table('drug_testing_policy')->where('applicant_id', $user->id)->count();

            return [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->user_avatar,
                'attempts' => (int) $user->attempts,
                'passed' => (int) $user->passed,
                'formsCompleted' => $formsCompleted,
                'score' => (float) $user->score,
            ];
        });

        return response()->json($users);
    }

    /**
     * ===============================
     * USERS LOCATION
     * ===============================
     */
    public function usersLocation()
    {
        $locations = DB::table('present_address')
            ->whereNotNull('present_state')
            ->where('present_state', '!=', '')
            ->select(
                'present_state as state',
                DB::raw('count(*) as count')
            )
            ->groupBy('present_state')
            ->orderByDesc('count')
            ->get();

        // ✅ APPLY NORMALIZATION HERE
        $locations = $locations->map(function ($item) {
            $item->state = $this->normalizeState($item->state);
            return $item;
        });

        return response()->json($locations);
    }

    private function normalizeState($state)
    {
        $map = [
            'AL' => 'Alabama',
            'AK' => 'Alaska',
            'AZ' => 'Arizona',
            'AR' => 'Arkansas',
            'CA' => 'California',
            'CO' => 'Colorado',
            'CT' => 'Connecticut',
            'DE' => 'Delaware',
            'FL' => 'Florida',
            'GA' => 'Georgia',
            'HI' => 'Hawaii',
            'ID' => 'Idaho',
            'IL' => 'Illinois',
            'IN' => 'Indiana',
            'IA' => 'Iowa',
            'KS' => 'Kansas',
            'KY' => 'Kentucky',
            'LA' => 'Louisiana',
            'ME' => 'Maine',
            'MD' => 'Maryland',
            'MA' => 'Massachusetts',
            'MI' => 'Michigan',
            'MN' => 'Minnesota',
            'MS' => 'Mississippi',
            'MO' => 'Missouri',
            'MT' => 'Montana',
            'NE' => 'Nebraska',
            'NV' => 'Nevada',
            'NH' => 'New Hampshire',
            'NJ' => 'New Jersey',
            'NM' => 'New Mexico',
            'NY' => 'New York',
            'NC' => 'North Carolina',
            'ND' => 'North Dakota',
            'OH' => 'Ohio',
            'OK' => 'Oklahoma',
            'OR' => 'Oregon',
            'PA' => 'Pennsylvania',
            'RI' => 'Rhode Island',
            'SC' => 'South Carolina',
            'SD' => 'South Dakota',
            'TN' => 'Tennessee',
            'TX' => 'Texas',
            'UT' => 'Utah',
            'VT' => 'Vermont',
            'VA' => 'Virginia',
            'WA' => 'Washington',
            'WV' => 'West Virginia',
            'WI' => 'Wisconsin',
            'WY' => 'Wyoming',
        ];

        return $map[$state] ?? $state;
    }

    /**
     * ===============================
     * GRAPH DATA
     * ===============================
     */
    public function graph()
    {
        $days = collect(range(6, 0))->map(function ($i) {
            return Carbon::today()->subDays($i)->format('Y-m-d');
        });

        $labels = [];
        $usersData = [];
        $quizData = [];

        foreach ($days as $day) {
            $labels[] = Carbon::parse($day)->format('D');

            $usersData[] = DB::table('users')
                ->whereDate('created_at', $day)
                ->count();

            $quizData[] = DB::table('attempts')
                ->whereDate('created_at', $day)
                ->count();
        }

        return response()->json([
            'labels' => $labels,
            'users' => $usersData,
            'quizzes' => $quizData,
        ]);
    }


    public function notifications()
    {
        $activities = collect();

        // 1. New users
        $users = DB::table('users')
            ->select(
                DB::raw('"New user registered" as title'),
                'created_at as date'
            )
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // 2. Quiz submissions (answers)
        $answers = DB::table('answers')
            ->select(
                DB::raw('"Quiz submitted" as title'),
                'created_at as date'
            )
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // 3. Verification updates
        $verifications = DB::table('verification')
            ->select(
                DB::raw('CONCAT("Verification ", signature) as title'),
                'created_at as date'
            )
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // 4. Delete requests
        $deletes = DB::table('request_delete')
            ->select(
                DB::raw('"Account deletion requested" as title'),
                'created_at as date'
            )
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // 5. Reports
        $reports = DB::table('reporting')
            ->select(
                DB::raw('"New report submitted" as title'),
                'created_at as date'
            )
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // merge all
        $activities = $activities
            ->merge($users)
            ->merge($answers)
            ->merge($verifications)
            ->merge($deletes)
            ->merge($reports)
            ->sortByDesc('date')
            ->take(10)
            ->values();

        return response()->json($activities);
    }
}
