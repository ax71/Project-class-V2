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
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->index('created_at');
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->index(['course_id', 'content_type']);
        });

        Schema::table('quiz_results', function (Blueprint $table) {
            $table->index(['user_id', 'quiz_id']);
            $table->index('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->dropIndex(['course_id', 'content_type']);
        });

        Schema::table('quiz_results', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'quiz_id']);
            $table->dropIndex(['completed_at']);
        });
    }
};
