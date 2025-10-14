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
        Schema::table('past_employment_info', function (Blueprint $table) {
            $table->string('salary_1');
            $table->string('salary_2')->nullable();
            $table->string('salary_3')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('past_employment_info', function (Blueprint $table) {
            //
        });
    }
};
