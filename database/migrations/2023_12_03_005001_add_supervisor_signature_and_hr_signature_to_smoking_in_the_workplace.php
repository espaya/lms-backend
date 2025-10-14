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
        Schema::table('smoking_in_the_workplace', function (Blueprint $table) {
            $table->string('supervisor_signature')->nullable();
            $table->string('hr_signature')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('smoking_in_the_workplace', function (Blueprint $table) {
            $table->dropColumn('supervisor_signature');
            $table->dropColumn('hr_signature');
        });
    }
};
