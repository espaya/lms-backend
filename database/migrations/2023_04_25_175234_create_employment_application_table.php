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
        Schema::create('employments_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id');
            $table->foreign('applicant_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('SSN');
            $table->string('furnish_work');
            $table->string('employment_desired');
            $table->string('position');
            $table->string('date_start');
            $table->string('salary');
            $table->string('employed_now');
            $table->string('inqure_present_employer')->nullable();
            $table->string('applied_before');
            $table->string('where');
            $table->string('when');
            $table->string('on_layoff_subject_to_recall');
            $table->string('travel_if_required');
            $table->string('relocate_if_required');
            $table->string('overtime_if_required');
            $table->string('attendance_requirements_position');
            $table->string('bonded');
            $table->string('convicted');
            $table->text('explain_convicted')->nullable();
            $table->string('drivers_license');
            $table->string('drivers_license_state');
            $table->text('special_skills_qualifications');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employments_applications');
    }
};
