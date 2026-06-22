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
        // To avoid SQLite alter table issues with indexes, we will add the new column and index.
        Schema::table('cbse_results', function (Blueprint $table) {
            $table->foreignUuid('academic_year_id')->nullable()->constrained('cbse_academic_years')->onDelete('cascade');
        });

        // We will keep exam_year but make it nullable in the code if we could, 
        // but since we can't easily alter it, we'll just leave it and stop using it,
        // or attempt to drop it if Laravel 11 supports it.
        Schema::table('cbse_results', function (Blueprint $table) {
            $table->dropUnique(['student_id', 'subject_id', 'exam_year']);
            $table->dropIndex(['student_id', 'exam_year']);
            $table->dropIndex(['exam_year']);
        });

        Schema::table('cbse_results', function (Blueprint $table) {
            $table->unique(['student_id', 'subject_id', 'academic_year_id'], 'cbse_results_student_sub_ay_unique');
            $table->index(['student_id', 'academic_year_id']);
            $table->index('academic_year_id');
        });
    }

    public function down(): void
    {
        Schema::table('cbse_results', function (Blueprint $table) {
            $table->dropUnique('cbse_results_student_sub_ay_unique');
            $table->dropIndex(['student_id', 'academic_year_id']);
            $table->dropIndex(['academic_year_id']);
        });

        Schema::table('cbse_results', function (Blueprint $table) {
            $table->unique(['student_id', 'subject_id', 'exam_year']);
            $table->index(['student_id', 'exam_year']);
            $table->index(['exam_year']);
        });

        Schema::table('cbse_results', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
        });
    }
};
