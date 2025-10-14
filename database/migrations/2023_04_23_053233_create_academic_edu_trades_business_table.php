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
        Schema::create('academic_trades_business', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id');
            $table->foreign('applicant_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('edu_current_name_location_school');
            $table->string('edu_current_number_years');
            $table->string('edu_current_did_graduate');
            $table->string('edu_current_subjects_studied');
            $table->string('edu_last_name_location_school');
            $table->string('edu_last_number_years');
            $table->string('edu_last_did_graduate');
            $table->string('edu_last_subjects_studied');
            $table->string('trades_current_name_location_school');
            $table->string('trades_current_number_years');
            $table->string('trades_current_did_graduate');
            $table->string('trades_current_subjects_studied');
            $table->string('trades_last_current_name_location_school');
            $table->string('trades_last_current_number_years');
            $table->string('trades_last_current_did_graduate');
            $table->string('trades_last_subjects_studied');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_trades_business');
    }
};
