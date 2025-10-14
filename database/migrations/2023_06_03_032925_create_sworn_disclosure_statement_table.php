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
        Schema::create('sworn_disclosure_statement', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id');
            $table->string('mailing_address');
            $table->string('convicted_outside_commonwealth');
            $table->string('outside_commonwealth_specify')->nullable();
            $table->string('convicted_pending');
            $table->string('convicted_pending_specify')->nullable();
            $table->string('child_abuse');
            $table->string('wit_signature');
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
        Schema::dropIfExists('sworn_disclosure_statement');
    }
};
