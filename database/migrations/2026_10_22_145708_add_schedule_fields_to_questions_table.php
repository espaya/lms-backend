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
        Schema::table('questions', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable()->after('slug');
            $table->timestamp('available_from')->nullable()->after('published_at');
            $table->timestamp('available_until')->nullable()->after('available_from');
            $table->unsignedInteger('duration_minutes')->nullable()->after('available_until');
            $table->timestamp('closed_at')->nullable()->after('duration_minutes');

            // Speeds up the "available now" query
            $table->index([
                'published_at',
                'available_from',
                'available_until',
                'closed_at'
            ], 'questions_availability_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex('questions_availability_idx');
            $table->dropColumn([
                'published_at',
                'available_from',
                'available_until',
                'duration_minutes',
                'closed_at',
            ]);
        });
    }
};
