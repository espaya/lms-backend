<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_profile_migration', function (Blueprint $table) {
            $table->id();
            $table->string('admin_fullname')->nullable();
            $table->string('admin_phone')->nullable();
            $table->string('admin_avatar')->nullable();
            $table->string('admin_address')->nullable();
            $table->bigInteger('admin_ID')->default(Auth::id());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_profile_migration');
    }
};
