<?php 

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UserStatisticsService
{
    public function countUnsignedApplications()
    {
        $tableCount = 0;
        
        //define an array of the tables
        $tableNames = ['verification', 'universal_precaution', 'sworn_disclosure_statement', 'smoking_in_the_workplace', 'sexual_harassment', 'reporting', 'policy_and_procedure', 'non_compete_agreement', 'infection_control_agreement', 'home_health_aide', 'health_safety_agreement', 'signature', 'employee_safety', 'employee_reference_check', 'employee_orientation', 'employee_dress_code', 'employee_conduct', 'employee_agreement', 'drug_testing_policy', 'disclaimer_waiver_liability', 'criminal_history_search', 'confidentiality'];

        foreach($tableNames as $tableName)
        {
            $count = DB::table($tableName)->whereNull('signature')->count();
            $tableCount += $count;
        }

        return $tableCount;
    }

    public function countApplications()
    {
        $tableCount = 0;
        
        //define an array of the tables
        $tableNames = ['verification', 'universal_precaution', 'sworn_disclosure_statement', 'smoking_in_the_workplace', 'sexual_harassment', 'reporting', 'policy_and_procedure', 'non_compete_agreement', 'infection_control_agreement', 'home_health_aide', 'health_safety_agreement', 'signature', 'employee_safety', 'employee_reference_check', 'employee_orientation', 'employee_dress_code', 'employee_conduct', 'employee_agreement', 'drug_testing_policy', 'disclaimer_waiver_liability', 'criminal_history_search', 'confidentiality'];

        foreach($tableNames as $tableName)
        {
            $count = DB::table($tableName)->whereNotNull('signature')->count();
            $tableCount += $count;
        }

        return $tableCount;
    }

    public function getUserCounts()
    {
        // get the first day of the current month
        $currentMonthStart = Carbon::now()->startOfMonth();
        // get the first day of the previous month
        $previousMonthStart = Carbon::now()->subMonth()->startOfMonth();
        // get the last day of the previous month
        $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        // calculate the month of users for the previous month
        $previousMonthCount = DB::table('users')->where('role', 'USER')->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])->count();
        // users created in the current month
        $currentMonthCount = DB::table('users')->where('role', 'USER')->whereBetween('created_at', [$currentMonthStart, Carbon::now()])->count();
        //calculate the percentage increase
        $percentageIncrease = ($currentMonthCount - $previousMonthCount) / ($previousMonthCount ?:1) * 100;

        return [
            'previousMonthCount' => $previousMonthCount,
            'currentMonthCount' => $currentMonthCount,
            'percentageIncrease' => $percentageIncrease,
        ];
    }
}