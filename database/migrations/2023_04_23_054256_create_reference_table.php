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
        Schema::create('reference', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id');
            $table->foreign('applicant_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('reference_name_1');
            $table->string('reference_address_1');
            $table->string('reference_phone_1');
            $table->string('reference_years_acquainted_1');
            $table->string('reference_name_2');
            $table->string('reference_address_2');
            $table->string('reference_phone_2');
            $table->string('reference_years_acquainted_2');
            $table->string('reference_name_3');
            $table->string('reference_address_3');
            $table->string('reference_phone_3');
            $table->string('reference_years_acquainted_3');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reference');
    }
};
