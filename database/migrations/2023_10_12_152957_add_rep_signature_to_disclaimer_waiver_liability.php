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
        Schema::table('disclaimer_waiver_liability', function (Blueprint $table) {
            $table->string('rep_signature')->nullable();
            $table->timestamp('rep_signed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('disclaimer_waiver_liability', function (Blueprint $table) {
            $table->dropColumn('rep_signature');
            $table->dropColumn('rep_signed_at');
        });
    }
};
