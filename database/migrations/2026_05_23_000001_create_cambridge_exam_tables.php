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
        Schema::create('qualifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('qualification_type', ['IGCSE', 'AS_LEVEL', 'A_LEVEL'])->unique();
            $table->string('qualification_name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('exam_series', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('series_code')->unique(); // e.g., "MAR-2024", "NOV-2024"
            $table->integer('year'); // 2024, 2025, etc.
            $table->enum('month', ['March', 'June', 'November']); // Dropdown options
            $table->string('series_name')->nullable(); // Auto-generated: "March 2024"
            $table->date('deadline_for_entry')->nullable();
            $table->date('result_publication_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['year', 'month']);
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('subject_code');
            $table->string('subject_name');
            $table->foreignUuid('qualification_id')->constrained('qualifications')->onDelete('cascade');
            $table->integer('total_marks');
            $table->decimal('passing_percentage', 5, 2)->default(40.00);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['qualification_id', 'subject_code']);
            $table->index('qualification_id');
        });

        Schema::create('components', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->string('component_code');
            $table->string('component_name');
            $table->enum('component_type', ['paper', 'practical', 'project', 'coursework', 'other']);
            $table->integer('total_marks');
            $table->integer('scaling_factor')->default(1);
            $table->boolean('is_mandatory')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['subject_id', 'component_code']);
            $table->index('subject_id');
        });

        Schema::create('candidates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('candidate_number');
            $table->string('candidate_name');
            $table->foreignUuid('school_id')->constrained('schools')->onDelete('cascade');
            $table->date('date_of_birth')->nullable();
            $table->char('gender', 1)->nullable();
            $table->date('enrollment_date');
            $table->enum('status', ['active', 'inactive', 'graduated'])->default('active');
            $table->timestamps();

            $table->unique(['school_id', 'candidate_number', 'candidate_name']);
            $table->index('candidate_number');
            $table->index('school_id');
        });

        Schema::create('grade_thresholds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('series_id')->constrained('exam_series')->onDelete('cascade');
            $table->foreignUuid('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->string('grade'); // Reference only for validation
            $table->enum('qualification_type', ['IGCSE', 'AS_LEVEL', 'A_LEVEL']);
            $table->decimal('minimum_pum', 5, 2); // Min PUM for this grade
            $table->decimal('maximum_pum', 5, 2)->nullable();
            $table->timestamps();
            $table->unique(['series_id', 'subject_id', 'grade']);
        });

        Schema::create('candidate_enrollments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->foreignUuid('series_id')->constrained('exam_series')->onDelete('cascade');
            $table->foreignUuid('qualification_id')->constrained('qualifications')->onDelete('cascade');
            $table->foreignUuid('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->enum('enrollment_status', ['enrolled', 'completed', 'withdrawn', 'absent'])->default('enrolled');
            $table->date('enrolled_date');
            $table->timestamps();

            $table->unique(['candidate_id', 'series_id', 'subject_id']);
            $table->index('candidate_id');
            $table->index('series_id');
            $table->index(['candidate_id', 'series_id', 'subject_id'], 'idx_candidate_series_subject');
        });

        Schema::create('subject_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('enrollment_id')->constrained('candidate_enrollments')->onDelete('cascade');
            $table->foreignUuid('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignUuid('series_id')->constrained('exam_series')->onDelete('cascade');
            
            // Direct upload fields (Phase 1)
            $table->string('grade'); // A*, A, B, C, D, E, U
            $table->decimal('pum', 5, 2); // Percentage Uniform Mark (out of 100)
            
            // Optional: Calculated from components (Phase 2)
            $table->decimal('total_obtained_marks', 10, 2)->nullable();
            $table->integer('total_marks')->nullable();
            $table->decimal('overall_percentage', 5, 2)->nullable();
            $table->decimal('calculated_uniform_mark', 10, 2)->nullable();
            $table->boolean('is_passed')->default(false);
            
            $table->text('remarks')->nullable();
            $table->enum('status', ['pending_components', 'component_marks_added', 'complete'])->default('pending_components');
            $table->timestamp('result_uploaded_at')->useCurrent();
            $table->timestamp('components_uploaded_at')->nullable();
            $table->foreignUuid('uploaded_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            
            $table->unique(['enrollment_id', 'subject_id', 'series_id']);
            $table->index('series_id');
            $table->index('grade');
        });

        Schema::create('component_marks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('subject_result_id')->constrained('subject_results')->onDelete('cascade');
            $table->foreignUuid('enrollment_id')->constrained('candidate_enrollments')->onDelete('cascade');
            $table->foreignUuid('component_id')->constrained('components')->onDelete('cascade');
            
            $table->decimal('obtained_marks', 10, 2);
            $table->integer('total_marks'); // From component definition
            $table->decimal('percentage', 5, 2); // (obtained/total) * 100
            
            $table->text('remarks')->nullable();
            $table->foreignUuid('uploaded_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();
            
            $table->unique(['subject_result_id', 'component_id']);
            $table->index(['subject_result_id', 'enrollment_id']);
        });

        Schema::create('upload_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('uploaded_by')->constrained('users')->onDelete('restrict');
            $table->foreignUuid('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignUuid('series_id')->constrained('exam_series')->onDelete('cascade');
            $table->foreignUuid('subject_id')->nullable()->constrained('subjects')->onDelete('set null');
            $table->string('file_name')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->enum('upload_type', ['component_marks', 'grade_thresholds', 'candidate_data']);
            $table->integer('records_processed')->default(0);
            $table->integer('records_failed')->default(0);
            $table->enum('status', ['success', 'partial', 'failed']);
            $table->longText('error_details')->nullable();
            $table->timestamp('uploaded_at')->useCurrent();

            $table->index('series_id');
            $table->index('school_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_logs');
        Schema::dropIfExists('component_marks');
        Schema::dropIfExists('subject_results');
        Schema::dropIfExists('candidate_enrollments');
        Schema::dropIfExists('grade_thresholds');
        Schema::dropIfExists('candidates');
        Schema::dropIfExists('components');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('exam_series');
        Schema::dropIfExists('qualifications');
    }
};
