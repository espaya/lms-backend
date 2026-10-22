<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('questions', fn(Blueprint $t) => $t->softDeletes());
        Schema::table('topics',    fn(Blueprint $t) => $t->softDeletes());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', fn(Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('topics',    fn(Blueprint $t) => $t->dropSoftDeletes());
    }
};
