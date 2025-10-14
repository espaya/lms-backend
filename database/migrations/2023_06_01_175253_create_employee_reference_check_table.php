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
        Schema::create('employee_reference_check', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id');
            $table->string('company_contacted');
            $table->string('employer_name');
            $table->string('from_date');
            $table->string('to_date');
            $table->string('eligible_for_hire');
            $table->string('comments');
            $table->string('received_by');
            $table->string('name_of_company');
            $table->string('signature');
            $table->string('rep_signature');
            $table->string('rep_title');
            $table->foreign('applicant_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_reference_check');
    }
};
