<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        Schema::dropIfExists('candidate_enrollments_new');

        Schema::create('candidate_enrollments_new', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->foreignUuid('series_id')->constrained('exam_series')->onDelete('cascade');
            $table->foreignUuid('qualification_id')->constrained('qualifications')->onDelete('cascade');
            $table->foreignUuid('subject_id')->nullable()->constrained('subjects')->onDelete('cascade');
            $table->enum('enrollment_status', ['enrolled', 'completed', 'withdrawn', 'absent'])->default('enrolled');
            $table->date('enrolled_date');
            $table->timestamps();

            $table->index('candidate_id');
            $table->index('series_id');
            $table->index(['candidate_id', 'series_id', 'subject_id'], 'idx_candidate_series_subject_new');
        });

        // Copy existing data
        DB::statement('INSERT INTO candidate_enrollments_new (id, candidate_id, series_id, qualification_id, subject_id, enrollment_status, enrolled_date, created_at, updated_at) SELECT id, candidate_id, series_id, qualification_id, subject_id, enrollment_status, enrolled_date, created_at, updated_at FROM candidate_enrollments');

        Schema::dropIfExists('candidate_enrollments');
        Schema::rename('candidate_enrollments_new', 'candidate_enrollments');

        DB::statement('PRAGMA foreign_keys = ON');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        Schema::dropIfExists('candidate_enrollments_new');

        Schema::create('candidate_enrollments_new', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->foreignUuid('series_id')->constrained('exam_series')->onDelete('cascade');
            $table->foreignUuid('qualification_id')->constrained('qualifications')->onDelete('cascade');
            $table->foreignUuid('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->enum('enrollment_status', ['enrolled', 'completed', 'withdrawn', 'absent'])->default('enrolled');
            $table->date('enrolled_date');
            $table->timestamps();

            $table->index('candidate_id');
            $table->index('series_id');
            $table->index(['candidate_id', 'series_id', 'subject_id'], 'idx_candidate_series_subject');
        });

        // Remove any rows where subject_id is null before restoring
        DB::table('candidate_enrollments')->whereNull('subject_id')->delete();

        DB::statement('INSERT INTO candidate_enrollments_new (id, candidate_id, series_id, qualification_id, subject_id, enrollment_status, enrolled_date, created_at, updated_at) SELECT id, candidate_id, series_id, qualification_id, subject_id, enrollment_status, enrolled_date, created_at, updated_at FROM candidate_enrollments');

        Schema::dropIfExists('candidate_enrollments');
        Schema::rename('candidate_enrollments_new', 'candidate_enrollments');

        DB::statement('PRAGMA foreign_keys = ON');
    }
};
