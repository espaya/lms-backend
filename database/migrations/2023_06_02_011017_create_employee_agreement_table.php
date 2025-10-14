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
        Schema::create('employee_agreement', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id');
            $table->string('monday_hour');
            $table->string('tuesday_hour');
            $table->string('wednesday_hour');
            $table->string('thursday_hour');
            $table->string('friday_hour');
            $table->string('saturday_hour');
            $table->string('sunday_hour');
            $table->string('time_off');
            $table->string('pay_per_hour');
            $table->string('other_agreements');
            $table->string('signature');
            $table->foreign('applicant_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_agreement');
    }
};
