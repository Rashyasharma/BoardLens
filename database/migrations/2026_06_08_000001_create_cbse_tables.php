<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // CBSE Qualifications (Class 10 & Class 12)
        Schema::create('cbse_qualifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('qualification_type', ['CLASS_10', 'CLASS_12'])->unique();
            $table->string('qualification_name'); // e.g. "Secondary (Class 10)"
            $table->string('board_code')->nullable(); // e.g. "241", "301"
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // CBSE Subjects
        Schema::create('cbse_subjects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('qualification_id')->constrained('cbse_qualifications')->onDelete('cascade');
            $table->string('subject_code');          // Official CBSE code e.g. "041"
            $table->string('subject_name');
            $table->integer('theory_marks')->default(80);
            $table->integer('practical_marks')->default(20); // Practical / Project / Internal Assessment
            $table->integer('total_marks')->storedAs('theory_marks + practical_marks');
            $table->string('practical_type')->default('Practical'); // "Practical", "Project", "Internal Assessment"
            $table->decimal('passing_percentage', 5, 2)->default(33.00);
            $table->decimal('theory_passing_marks', 5, 2)->default(26.40); // 33% of theory_marks
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['qualification_id', 'subject_code']);
            $table->index('qualification_id');
        });

        // CBSE Students
        Schema::create('cbse_students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('admission_number');     // School's internal ID
            $table->string('student_name');
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['M', 'F', 'O'])->nullable();
            $table->enum('qualification_type', ['CLASS_10', 'CLASS_12'])->nullable();
            $table->integer('admission_year')->nullable();
            $table->enum('status', ['active', 'passed', 'failed', 'transferred'])->default('active');
            $table->timestamps();

            $table->unique('admission_number');
            $table->index('student_name');
        });

        // CBSE Results (year-wise — no exam series)
        Schema::create('cbse_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('cbse_students')->onDelete('cascade');
            $table->foreignUuid('qualification_id')->constrained('cbse_qualifications')->onDelete('cascade');
            $table->foreignUuid('subject_id')->constrained('cbse_subjects')->onDelete('cascade');

            // Year is the only time dimension (no exam series)
            $table->integer('exam_year');           // e.g. 2024

            // Roll number assigned by CBSE board for that year
            $table->string('roll_number')->nullable();

            // Marks breakdown
            $table->decimal('theory_obtained', 6, 2)->nullable();
            $table->decimal('practical_obtained', 6, 2)->nullable();
            $table->decimal('total_obtained', 6, 2)->nullable();
            $table->integer('total_marks')->nullable();
            $table->decimal('percentage', 5, 2)->nullable(); // out of 100

            // CBSE Grade (A1, A2, B1, B2, C1, C2, D, E1, E2)
            $table->string('grade')->nullable();

            // Pass/Fail
            $table->boolean('is_passed')->default(false);
            $table->boolean('is_absent')->default(false);
            $table->boolean('is_compartment')->default(false); // Compartment exam needed

            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'exam_year']);
            $table->index(['student_id', 'exam_year']);
            $table->index('exam_year');
            $table->index('grade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbse_results');
        Schema::dropIfExists('cbse_students');
        Schema::dropIfExists('cbse_subjects');
        Schema::dropIfExists('cbse_qualifications');
    }
};
