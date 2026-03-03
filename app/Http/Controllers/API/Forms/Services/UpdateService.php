<?php

namespace App\Http\Controllers\services;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/* this class is for update the administrative signatures of the application forms */

class UpdateService
{
    public function updateRecord($tableName, $conditions, $newValues)
    {
        try {
            return DB::table($tableName)->where($conditions)->update($newValues);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
        }
    }
}
