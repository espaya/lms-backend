<?php

namespace App\Http\Controllers\services;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/* this class is for update the administrative signatures of the application forms */

class UpdateService 
{
    public function updateRecord($tableName, $conditions, $newValues)
    {
        return DB::table($tableName)->where($conditions)->update($newValues);
    }
}
