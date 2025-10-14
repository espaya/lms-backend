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
        Schema::create('verification', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id');
            $table->string('disciplines');
            $table->string('licenseNumber');
            $table->string('expirationDate');
            $table->string('dateVerified');
            $table->string('licenseVerifiedBy');
            $table->string('actionOutstanding');
            $table->string('comments');
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
        Schema::dropIfExists('verification');
    }
};
