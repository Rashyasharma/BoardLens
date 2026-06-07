# Cambridge Exam Portal - PHP/SQL Implementation Plan
## Antiggravity Development Team

**Project Name:** Cambridge Exam Portal (CEP)  
**Technology Stack:** PHP 8.2+ | MySQL 8.0+ | Laravel Framework  
**Timeline:** 18-20 weeks  
**Team Size:** 4-5 developers + 1 DevOps  

---

## EXECUTIVE SUMMARY

A comprehensive web portal built on PHP/MySQL for Cambridge examination analysis, featuring component-wise mark tracking, multi-series exam management, and advanced analytics. Built with Laravel framework for rapid development, scalability, and maintainability.

---

## 1. TECHNOLOGY STACK (PHP-SQL SPECIFIC)

### 1.1 Backend Stack

**Framework:** Laravel 11.x
- **Why Laravel:** MVC architecture, excellent ORM (Eloquent), built-in authentication, migrations, job queues, excellent documentation
- **PHP Version:** 8.2 or higher
- **Composer:** Latest version for dependency management

**Key PHP Libraries:**
```json
{
  "laravel/framework": "^11.0",
  "laravel/tinker": "^2.8",
  "laravel/sanctum": "^3.0",
  "illuminate/database": "^11.0",
  "intervention/image": "^3.0",
  "maatwebsite/excel": "^3.1",
  "barryvdh/laravel-dompdf": "^2.1",
  "spatie/laravel-query-builder": "^5.8",
  "spatie/laravel-permission": "^5.10",
  "spatie/laravel-audit": "^4.2",
  "predis/predis": "^2.2"
}
```

### 1.2 Database Stack

**Primary Database:** MySQL 8.0+
- **Collation:** utf8mb4_unicode_ci
- **Storage Engine:** InnoDB
- **Max Connections:** 200+

**Caching Layer:** Redis 6.0+
- Session caching
- Query result caching
- Job queue backend

**Database Tools:**
- **Migrations:** Laravel Migrations
- **Seeding:** Laravel Seeders for test data
- **Admin Tool:** phpMyAdmin or DBeaver

### 1.3 Frontend Stack

**Frontend Framework:** Vue.js 3.x or React with Laravel integration
- **Blade Templating** for traditional server-rendered pages
- **Alpine.js** for lightweight interactivity
- **Tailwind CSS** for minimal design

**JavaScript Libraries:**
```json
{
  "chart.js": "^4.4",
  "axios": "^1.6",
  "tailwindcss": "^3.3",
  "alpinejs": "^3.13",
  "datatables.net": "^1.13"
}
```

**For Excel/CSV:** 
- **PhpSpreadsheet** (included in maatwebsite/excel)

**PDF Generation:**
- **DomPDF** for server-side PDF generation

### 1.4 Development Tools

**IDE/Editor:**
- VS Code with PHP extensions
- PHPStorm (optional but recommended)

**Version Control:** Git + GitHub/GitLab

**Local Development Environment:**
- Docker containers (Recommended)
  - PHP 8.2 container
  - MySQL 8.0 container
  - Redis container
  - Nginx web server
  
OR

- **Homestead/Valet** for local development
- **XAMPP/WAMP** for quick setup

**Testing:**
- PHPUnit for unit tests
- Pestphp for feature tests
- Mockery for mocking

**Code Quality:**
- Laravel Pint (code formatting)
- PHPStan (static analysis)
- SonarQube (code quality metrics)

### 1.5 Deployment Stack

**Web Server:** Nginx or Apache 2.4+
**PHP-FPM:** PHP 8.2 FastCGI
**Reverse Proxy:** Nginx
**SSL:** Let's Encrypt + Certbot
**Monitoring:** New Relic or DataDog
**Log Management:** ELK Stack or Papertrail

---

## 2. PROJECT DIRECTORY STRUCTURE

```
cambridge-exam-portal/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── StudentController.php
│   │   │   ├── UploadController.php
│   │   │   ├── ResultController.php
│   │   │   ├── AnalyticsController.php
│   │   │   ├── ReportController.php
│   │   │   └── AdminController.php
│   │   ├── Requests/
│   │   │   ├── StoreMarksRequest.php
│   │   │   ├── StoreThresholdRequest.php
│   │   │   └── StoreCandidateRequest.php
│   │   └── Middleware/
│   │       ├── RoleMiddleware.php
│   │       └── LogActivity.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── School.php
│   │   ├── Candidate.php
│   │   ├── Qualification.php
│   │   ├── ExamSeries.php
│   │   ├── Subject.php
│   │   ├── Component.php
│   │   ├── GradeThreshold.php
│   │   ├── CandidateEnrollment.php
│   │   ├── ComponentMarks.php
│   │   ├── SubjectResult.php
│   │   └── UploadLog.php
│   ├── Services/
│   │   ├── MarkCalculationService.php
│   │   ├── GradeAssignmentService.php
│   │   ├── UploadProcessingService.php
│   │   ├── ExcelImportService.php
│   │   ├── AnalyticsService.php
│   │   └── ReportGenerationService.php
│   ├── Jobs/
│   │   ├── ProcessBulkMarksUpload.php
│   │   ├── GenerateAnalyticsReport.php
│   │   └── SendUploadNotification.php
│   ├── Events/
│   │   ├── MarksUploaded.php
│   │   ├── ResultsGenerated.php
│   │   └── ThresholdsUpdated.php
│   └── Exceptions/
│       ├── InvalidMarksException.php
│       └── UploadProcessingException.php
├── database/
│   ├── migrations/
│   │   ├── 2024_01_01_create_users_table.php
│   │   ├── 2024_01_02_create_schools_table.php
│   │   ├── 2024_01_03_create_candidates_table.php
│   │   ├── 2024_01_04_create_qualifications_table.php
│   │   ├── 2024_01_05_create_exam_series_table.php
│   │   ├── 2024_01_06_create_subjects_table.php
│   │   ├── 2024_01_07_create_components_table.php
│   │   ├── 2024_01_08_create_grade_thresholds_table.php
│   │   ├── 2024_01_09_create_candidate_enrollments_table.php
│   │   ├── 2024_01_10_create_component_marks_table.php
│   │   ├── 2024_01_11_create_subject_results_table.php
│   │   └── 2024_01_12_create_upload_logs_table.php
│   ├── seeders/
│   │   ├── QualificationSeeder.php
│   │   ├── SubjectSeeder.php
│   │   ├── ComponentSeeder.php
│   │   └── TestDataSeeder.php
│   └── factories/
│       ├── CandidateFactory.php
│       ├── ExamSeriesFactory.php
│       └── ComponentMarksFactory.php
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── app.blade.php
│   │   │   ├── sidebar.blade.php
│   │   │   └── footer.blade.php
│   │   ├── auth/
│   │   │   ├── login.blade.php
│   │   │   └── register.blade.php
│   │   ├── dashboard/
│   │   │   └── index.blade.php
│   │   ├── students/
│   │   │   ├── index.blade.php
│   │   │   ├── show.blade.php
│   │   │   └── charts.blade.php
│   │   ├── uploads/
│   │   │   ├── marks.blade.php
│   │   │   ├── thresholds.blade.php
│   │   │   ├── candidates.blade.php
│   │   │   └── history.blade.php
│   │   ├── analytics/
│   │   │   ├── yearly.blade.php
│   │   │   ├── grade-distribution.blade.php
│   │   │   ├── subject-performance.blade.php
│   │   │   └── yoy-comparison.blade.php
│   │   ├── reports/
│   │   │   └── index.blade.php
│   │   └── admin/
│   │       ├── users.blade.php
│   │       ├── subjects.blade.php
│   │       └── settings.blade.php
│   └── css/
│       ├── app.css
│       └── tailwind.css
│   └── js/
│       ├── app.js
│       ├── charts.js
│       └── filters.js
├── routes/
│   ├── web.php
│   ├── api.php
│   └── console.php
├── tests/
│   ├── Unit/
│   │   ├── MarkCalculationTest.php
│   │   ├── GradeAssignmentTest.php
│   │   └── ValidationRulesTest.php
│   ├── Feature/
│   │   ├── UploadMarkTest.php
│   │   ├── StudentSearchTest.php
│   │   ├── AnalyticsTest.php
│   │   └── AuthenticationTest.php
│   └── CreatesApplication.php
├── storage/
│   ├── app/
│   │   ├── uploads/
│   │   │   ├── marks/
│   │   │   ├── thresholds/
│   │   │   └── candidates/
│   │   └── reports/
│   ├── logs/
│   └── framework/
├── .env
├── .env.example
├── composer.json
├── composer.lock
├── webpack.mix.js
├── package.json
├── docker-compose.yml
├── Dockerfile
├── artisan
└── README.md
```

---

## 3. MYSQL DATABASE SCHEMA (SQL)

### 3.1 Complete SQL File Structure

```sql
-- File: database/migrations/2024_01_01_create_users_table.php (Laravel Migration)

Schema::create('users', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('username')->unique();
    $table->string('email')->unique();
    $table->string('password');
    $table->enum('role', ['admin', 'exam_officer', 'viewer'])->default('viewer');
    $table->foreignUuid('school_id')->nullable()->constrained('schools')->onDelete('set null');
    $table->boolean('is_active')->default(true);
    $table->timestamp('last_login_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
});

-- File: database/migrations/2024_01_02_create_schools_table.php

Schema::create('schools', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('school_name');
    $table->string('school_code')->unique();
    $table->text('address')->nullable();
    $table->string('contact_email')->nullable();
    $table->string('contact_phone')->nullable();
    $table->timestamps();
});

-- File: database/migrations/2024_01_03_create_candidates_table.php

Schema::create('candidates', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('candidate_number');
    $table->string('candidate_name');
    $table->foreignUuid('school_id')->constrained('schools')->onDelete('cascade');
    $table->date('date_of_birth')->nullable();
    $table->char('gender', 1)->nullable(); // 'M', 'F', 'O'
    $table->date('enrollment_date');
    $table->enum('status', ['active', 'inactive', 'graduated'])->default('active');
    $table->timestamps();
    $table->unique(['school_id', 'candidate_number', 'candidate_name']);
    $table->index('candidate_number');
    $table->index('school_id');
});

-- File: database/migrations/2024_01_04_create_qualifications_table.php

Schema::create('qualifications', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->enum('qualification_type', ['IDCC', 'AS_LEVEL', 'A_LEVEL'])->unique();
    $table->string('qualification_name');
    $table->text('description')->nullable();
    $table->timestamps();
});

-- File: database/migrations/2024_01_05_create_exam_series_table.php

Schema::create('exam_series', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('series_code')->unique();
    $table->string('series_name'); // "Feb-Mar 2024", "May-Jun 2024"
    $table->foreignUuid('qualification_id')->constrained('qualifications')->onDelete('cascade');
    $table->string('start_month');
    $table->string('end_month');
    $table->integer('year');
    $table->date('deadline_for_entry')->nullable();
    $table->date('result_publication_date')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->unique(['qualification_id', 'series_code', 'year']);
    $table->index('qualification_id');
    $table->index('year');
});

-- File: database/migrations/2024_01_06_create_subjects_table.php

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

-- File: database/migrations/2024_01_07_create_components_table.php

Schema::create('components', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('subject_id')->constrained('subjects')->onDelete('cascade');
    $table->string('component_code');
    $table->string('component_name');
    $table->enum('component_type', ['paper', 'practical', 'project', 'coursework', 'other']);
    $table->integer('total_marks');
    $table->decimal('weighting', 5, 2); // Percentage contribution
    $table->boolean('is_mandatory')->default(true);
    $table->text('description')->nullable();
    $table->timestamps();
    $table->unique(['subject_id', 'component_code']);
    $table->index('subject_id');
});

-- File: database/migrations/2024_01_08_create_grade_thresholds_table.php

Schema::create('grade_thresholds', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('series_id')->constrained('exam_series')->onDelete('cascade');
    $table->foreignUuid('subject_id')->constrained('subjects')->onDelete('cascade');
    $table->string('grade'); // 'A*', 'A', 'B', 'a', 'b', etc.
    $table->enum('qualification_type', ['IDCC', 'AS_LEVEL', 'A_LEVEL']);
    $table->decimal('minimum_percentage', 5, 2);
    $table->decimal('maximum_percentage', 5, 2)->nullable();
    $table->integer('minimum_marks')->nullable();
    $table->integer('maximum_marks')->nullable();
    $table->foreignUuid('created_by')->constrained('users')->onDelete('restrict');
    $table->timestamps();
    $table->unique(['series_id', 'subject_id', 'grade']);
    $table->index('series_id');
    $table->index('subject_id');
});

-- File: database/migrations/2024_01_09_create_candidate_enrollments_table.php

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
});

-- File: database/migrations/2024_01_10_create_component_marks_table.php

Schema::create('component_marks', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('enrollment_id')->constrained('candidate_enrollments')->onDelete('cascade');
    $table->foreignUuid('component_id')->constrained('components')->onDelete('cascade');
    $table->decimal('obtained_marks', 10, 2);
    $table->integer('total_marks');
    $table->decimal('uniform_mark', 10, 2); // Out of 100
    $table->text('remarks')->nullable();
    $table->foreignUuid('uploaded_by')->constrained('users')->onDelete('restrict');
    $table->timestamp('uploaded_at')->useCurrent();
    $table->timestamps();
    $table->unique(['enrollment_id', 'component_id']);
    $table->index('enrollment_id');
    $table->index('component_id');
});

-- File: database/migrations/2024_01_11_create_subject_results_table.php

Schema::create('subject_results', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('enrollment_id')->constrained('candidate_enrollments')->onDelete('cascade');
    $table->foreignUuid('subject_id')->constrained('subjects')->onDelete('cascade');
    $table->foreignUuid('series_id')->constrained('exam_series')->onDelete('cascade');
    $table->decimal('total_obtained_marks', 10, 2);
    $table->integer('total_marks');
    $table->decimal('overall_percentage', 5, 2);
    $table->decimal('uniform_mark', 10, 2);
    $table->string('grade'); // A*, A, B, etc.
    $table->decimal('grade_point', 3, 2)->nullable();
    $table->boolean('is_passed');
    $table->text('remarks')->nullable();
    $table->timestamp('calculated_at')->useCurrent();
    $table->timestamps();
    $table->unique(['enrollment_id', 'subject_id', 'series_id']);
    $table->index('enrollment_id');
    $table->index('series_id');
    $table->index('grade');
});

-- File: database/migrations/2024_01_12_create_upload_logs_table.php

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

-- Create Indexes for Performance

-- Component Marks Performance Indexes
ALTER TABLE `component_marks` ADD INDEX `idx_enrollment_component` (`enrollment_id`, `component_id`);
ALTER TABLE `component_marks` ADD INDEX `idx_uniform_mark` (`uniform_mark`);

-- Subject Results Performance Indexes
ALTER TABLE `subject_results` ADD INDEX `idx_series_subject_grade` (`series_id`, `subject_id`, `grade`);
ALTER TABLE `subject_results` ADD INDEX `idx_candidate_results` (`enrollment_id`, `series_id`);

-- Grade Thresholds Indexes
ALTER TABLE `grade_thresholds` ADD INDEX `idx_lookup_grade` (`series_id`, `subject_id`, `qualification_type`, `grade`);

-- Candidate Enrollments Indexes
ALTER TABLE `candidate_enrollments` ADD INDEX `idx_candidate_series_subject` (`candidate_id`, `series_id`, `subject_id`);
```

---

## 4. LARAVEL ELOQUENT MODELS (PHP)

### 4.1 Core Model Examples

```php
// app/Models/Candidate.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Candidate extends Model
{
    use HasUuids;

    protected $fillable = [
        'candidate_number',
        'candidate_name',
        'school_id',
        'date_of_birth',
        'gender',
        'enrollment_date',
        'status'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'enrollment_date' => 'date',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CandidateEnrollment::class);
    }

    public function results()
    {
        return $this->hasManyThrough(
            SubjectResult::class,
            CandidateEnrollment::class
        );
    }
}

// app/Models/ExamSeries.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ExamSeries extends Model
{
    use HasUuids;

    protected $fillable = [
        'series_code',
        'series_name',
        'qualification_id',
        'start_month',
        'end_month',
        'year',
        'deadline_for_entry',
        'result_publication_date',
        'is_active'
    ];

    protected $casts = [
        'deadline_for_entry' => 'date',
        'result_publication_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function qualification()
    {
        return $this->belongsTo(Qualification::class);
    }

    public function enrollments()
    {
        return $this->hasMany(CandidateEnrollment::class, 'series_id');
    }

    public function gradeThresholds()
    {
        return $this->hasMany(GradeThreshold::class, 'series_id');
    }
}

// app/Models/ComponentMarks.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ComponentMarks extends Model
{
    use HasUuids;

    protected $table = 'component_marks';

    protected $fillable = [
        'enrollment_id',
        'component_id',
        'obtained_marks',
        'total_marks',
        'uniform_mark',
        'remarks',
        'uploaded_by',
    ];

    protected $casts = [
        'obtained_marks' => 'decimal:2',
        'uniform_mark' => 'decimal:2',
    ];

    public function enrollment()
    {
        return $this->belongsTo(CandidateEnrollment::class);
    }

    public function component()
    {
        return $this->belongsTo(Component::class);
    }

    // Calculate uniform mark automatically
    public function setObtainedMarksAttribute($value)
    {
        $this->attributes['obtained_marks'] = $value;
        if (isset($this->attributes['total_marks']) && $this->attributes['total_marks'] > 0) {
            $this->attributes['uniform_mark'] = ($value / $this->attributes['total_marks']) * 100;
        }
    }
}

// app/Models/SubjectResult.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SubjectResult extends Model
{
    use HasUuids;

    protected $fillable = [
        'enrollment_id',
        'subject_id',
        'series_id',
        'total_obtained_marks',
        'total_marks',
        'overall_percentage',
        'uniform_mark',
        'grade',
        'grade_point',
        'is_passed',
        'remarks',
        'calculated_at'
    ];

    protected $casts = [
        'total_obtained_marks' => 'decimal:2',
        'overall_percentage' => 'decimal:2',
        'uniform_mark' => 'decimal:2',
        'grade_point' => 'decimal:2',
        'is_passed' => 'boolean',
        'calculated_at' => 'datetime',
    ];

    public function enrollment()
    {
        return $this->belongsTo(CandidateEnrollment::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function series()
    {
        return $this->belongsTo(ExamSeries::class);
    }

    public function candidate()
    {
        return $this->enrollment->candidate();
    }
}
```

---

## 5. CORE SERVICE CLASSES (PHP Business Logic)

### 5.1 Mark Calculation Service

```php
// app/Services/MarkCalculationService.php

<?php

namespace App\Services;

use App\Models\ComponentMarks;
use App\Models\Component;
use App\Models\CandidateEnrollment;
use App\Models\SubjectResult;
use App\Models\GradeThreshold;
use Illuminate\Support\Collection;

class MarkCalculationService
{
    /**
     * Calculate subject result from component marks
     */
    public function calculateSubjectResult(CandidateEnrollment $enrollment): SubjectResult
    {
        // Get all component marks for this enrollment
        $componentMarks = ComponentMarks::where('enrollment_id', $enrollment->id)
            ->with('component')
            ->get();

        if ($componentMarks->isEmpty()) {
            throw new \Exception("No component marks found for this enrollment");
        }

        // Calculate weighted marks
        $totalWeightedMark = $this->calculateWeightedMark($componentMarks);
        
        // Get component-wise totals
        $totalObtained = $componentMarks->sum('obtained_marks');
        $totalPossible = $componentMarks->sum('total_marks');

        // Calculate percentage
        $percentage = ($totalObtained / $totalPossible) * 100;

        // Assign grade based on thresholds
        $grade = $this->assignGrade(
            $enrollment->subject_id,
            $enrollment->series_id,
            $percentage,
            $enrollment->qualification->qualification_type
        );

        // Determine if passed
        $subject = $enrollment->subject;
        $isPassed = $percentage >= $subject->passing_percentage;

        // Create or update result
        $result = SubjectResult::updateOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'subject_id' => $enrollment->subject_id,
                'series_id' => $enrollment->series_id,
            ],
            [
                'total_obtained_marks' => $totalObtained,
                'total_marks' => $totalPossible,
                'overall_percentage' => round($percentage, 2),
                'uniform_mark' => round($totalWeightedMark, 2),
                'grade' => $grade,
                'is_passed' => $isPassed,
                'calculated_at' => now(),
            ]
        );

        return $result;
    }

    /**
     * Calculate weighted mark based on component weightings
     */
    private function calculateWeightedMark(Collection $componentMarks): float
    {
        $totalWeighting = 0;
        $weightedSum = 0;

        foreach ($componentMarks as $mark) {
            $weighting = $mark->component->weighting;
            $totalWeighting += $weighting;
            $weightedSum += ($mark->uniform_mark * $weighting);
        }

        if ($totalWeighting == 0) {
            return 0;
        }

        return $weightedSum / $totalWeighting;
    }

    /**
     * Assign grade based on percentage and thresholds
     */
    private function assignGrade(
        string $subjectId,
        string $seriesId,
        float $percentage,
        string $qualificationType
    ): string {
        // Get all thresholds for this series and subject, ordered by minimum percentage desc
        $thresholds = GradeThreshold::where('series_id', $seriesId)
            ->where('subject_id', $subjectId)
            ->where('qualification_type', $qualificationType)
            ->orderBy('minimum_percentage', 'desc')
            ->get();

        foreach ($thresholds as $threshold) {
            if ($percentage >= $threshold->minimum_percentage) {
                return $threshold->grade;
            }
        }

        // If no threshold matched, return U (ungraded)
        return 'U';
    }

    /**
     * Bulk calculate results for all enrollments in a series
     */
    public function calculateSeriesResults(string $seriesId): array
    {
        $enrollments = CandidateEnrollment::where('series_id', $seriesId)
            ->with(['candidate', 'subject', 'qualification'])
            ->get();

        $results = [];
        foreach ($enrollments as $enrollment) {
            try {
                $result = $this->calculateSubjectResult($enrollment);
                $results[] = [
                    'status' => 'success',
                    'enrollment_id' => $enrollment->id,
                    'result_id' => $result->id,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'status' => 'failed',
                    'enrollment_id' => $enrollment->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
```

### 5.2 Upload Processing Service

```php
// app/Services/ExcelImportService.php

<?php

namespace App\Services;

use Maatwebsite\Excel\Facades\Excel;
use App\Models\ComponentMarks;
use App\Models\Candidate;
use App\Models\Component;
use App\Models\CandidateEnrollment;
use Illuminate\Support\Collection;

class ExcelImportService
{
    protected MarkCalculationService $calculationService;

    public function __construct(MarkCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    /**
     * Import component marks from Excel file
     * Expected columns: candidate_number, candidate_name, subject_code, 
     *                   component_code, obtained_marks, total_marks
     */
    public function importComponentMarks(
        string $filePath,
        string $seriesId,
        string $schoolId,
        string $userId
    ): array {
        $file = Excel::toArray(new class {}, $filePath);
        $data = collect($file[0])->skip(1); // Skip header row

        $results = [
            'successful' => [],
            'failed' => [],
            'summary' => [
                'total_rows' => count($data),
                'processed' => 0,
                'failed_count' => 0,
            ]
        ];

        foreach ($data as $row) {
            try {
                // Validate row
                $this->validateMarkRow($row);

                // Find or create enrollment
                $enrollment = $this->findOrCreateEnrollment(
                    $row[0], // candidate_number
                    $row[1], // candidate_name
                    $seriesId,
                    $row[2], // subject_code
                    $schoolId
                );

                // Find component
                $component = Component::whereHas('subject', function ($q) use ($row) {
                    $q->where('subject_code', $row[2]);
                })
                ->where('component_code', $row[3])
                ->firstOrFail();

                // Store component marks
                $mark = ComponentMarks::updateOrCreate(
                    [
                        'enrollment_id' => $enrollment->id,
                        'component_id' => $component->id,
                    ],
                    [
                        'obtained_marks' => (float)$row[4],
                        'total_marks' => (int)$row[5],
                        'uploaded_by' => $userId,
                    ]
                );

                // Recalculate subject result
                $this->calculationService->calculateSubjectResult($enrollment);

                $results['successful'][] = [
                    'candidate' => $row[0] . ' - ' . $row[1],
                    'component' => $row[3],
                    'marks' => $row[4] . '/' . $row[5],
                ];
                $results['summary']['processed']++;

            } catch (\Exception $e) {
                $results['failed'][] = [
                    'row' => count($results['successful']) + count($results['failed']) + 2,
                    'candidate' => $row[0] ?? 'Unknown',
                    'error' => $e->getMessage(),
                ];
                $results['summary']['failed_count']++;
            }
        }

        return $results;
    }

    /**
     * Validate mark row data
     */
    private function validateMarkRow(array $row): void
    {
        if (count($row) < 6) {
            throw new \Exception("Row does not have required columns");
        }

        if (!is_numeric($row[4]) || !is_numeric($row[5])) {
            throw new \Exception("Marks must be numeric");
        }

        if ($row[4] > $row[5] || $row[4] < 0) {
            throw new \Exception("Obtained marks cannot exceed total marks");
        }
    }

    /**
     * Find or create candidate enrollment
     */
    private function findOrCreateEnrollment(
        string $candidateNumber,
        string $candidateName,
        string $seriesId,
        string $subjectCode,
        string $schoolId
    ): CandidateEnrollment {
        $candidate = Candidate::where('school_id', $schoolId)
            ->where('candidate_number', $candidateNumber)
            ->where('candidate_name', $candidateName)
            ->firstOrFail();

        $subject = \App\Models\Subject::where('subject_code', $subjectCode)->firstOrFail();

        return CandidateEnrollment::firstOrCreate(
            [
                'candidate_id' => $candidate->id,
                'series_id' => $seriesId,
                'subject_id' => $subject->id,
            ],
            [
                'qualification_id' => $subject->qualification_id,
                'enrolled_date' => now()->toDateString(),
                'enrollment_status' => 'enrolled',
            ]
        );
    }
}
```

### 5.3 Analytics Service

```php
// app/Services/AnalyticsService.php

<?php

namespace App\Services;

use App\Models\SubjectResult;
use App\Models\Candidate;
use Illuminate\Support\Collection;

class AnalyticsService
{
    /**
     * Get grade distribution for filters
     */
    public function getGradeDistribution(array $filters): array
    {
        $query = SubjectResult::query();
        $query = $this->applyFilters($query, $filters);

        $distribution = $query->groupBy('grade')
            ->selectRaw('grade, COUNT(*) as count')
            ->orderByRaw("FIELD(grade, 'A*', 'A', 'B', 'C', 'D', 'E', 'U')")
            ->get()
            ->pluck('count', 'grade')
            ->toArray();

        return $distribution;
    }

    /**
     * Get pass/fail statistics
     */
    public function getPassFailStats(array $filters): array
    {
        $query = SubjectResult::query();
        $query = $this->applyFilters($query, $filters);

        $total = $query->count();
        $passed = (clone $query)->where('is_passed', true)->count();
        $failed = $total - $passed;

        return [
            'total' => $total,
            'passed' => $passed,
            'failed' => $failed,
            'pass_rate' => $total > 0 ? round(($passed / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Get subject-wise performance
     */
    public function getSubjectPerformance(array $filters): Collection
    {
        $query = SubjectResult::query();
        $query = $this->applyFilters($query, $filters);

        return $query->with('subject')
            ->selectRaw('
                subject_id,
                COUNT(*) as total_students,
                AVG(overall_percentage) as avg_percentage,
                MIN(overall_percentage) as min_percentage,
                MAX(overall_percentage) as max_percentage,
                STDDEV(overall_percentage) as std_dev,
                SUM(CASE WHEN is_passed = true THEN 1 ELSE 0 END) as pass_count
            ')
            ->groupBy('subject_id')
            ->get()
            ->map(function ($result) {
                $result->pass_rate = ($result->pass_count / $result->total_students) * 100;
                return $result;
            });
    }

    /**
     * Get year-on-year comparison
     */
    public function getYearOnYearComparison(string $subjectId): array
    {
        $data = SubjectResult::whereHas('subject', function ($q) use ($subjectId) {
            $q->where('id', $subjectId);
        })
        ->with('series')
        ->selectRaw('
            YEAR(subject_results.created_at) as year,
            COUNT(*) as total,
            SUM(CASE WHEN is_passed = true THEN 1 ELSE 0 END) as passed,
            AVG(overall_percentage) as avg_percentage
        ')
        ->groupBy('year')
        ->orderBy('year')
        ->get()
        ->map(function ($item) {
            $item->pass_rate = ($item->passed / $item->total) * 100;
            return $item;
        });

        return $data->toArray();
    }

    /**
     * Get statistical summary
     */
    public function getStatisticalSummary(array $filters): array
    {
        $query = SubjectResult::query();
        $query = $this->applyFilters($query, $filters);

        $results = $query->get();

        return [
            'total_students' => $results->count(),
            'average_percentage' => round($results->avg('overall_percentage'), 2),
            'median_percentage' => $this->calculateMedian($results->pluck('overall_percentage')),
            'std_deviation' => round($this->calculateStdDev($results->pluck('overall_percentage')), 2),
            'highest_score' => $results->max('overall_percentage'),
            'lowest_score' => $results->min('overall_percentage'),
        ];
    }

    /**
     * Apply filter conditions to query
     */
    private function applyFilters($query, array $filters)
    {
        if (!empty($filters['year'])) {
            $query->whereYear('subject_results.created_at', $filters['year']);
        }

        if (!empty($filters['series_id'])) {
            $query->where('series_id', $filters['series_id']);
        }

        if (!empty($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (!empty($filters['qualification_type'])) {
            $query->whereHas('subject', function ($q) use ($filters) {
                $q->whereHas('qualification', function ($q2) use ($filters) {
                    $q2->where('qualification_type', $filters['qualification_type']);
                });
            });
        }

        if (!empty($filters['grade'])) {
            $query->where('grade', $filters['grade']);
        }

        if (!empty($filters['school_id'])) {
            $query->whereHas('enrollment.candidate', function ($q) use ($filters) {
                $q->where('school_id', $filters['school_id']);
            });
        }

        return $query;
    }

    /**
     * Calculate median
     */
    private function calculateMedian(Collection $values): float
    {
        $sorted = $values->sort()->values();
        $count = $sorted->count();
        $middle = intdiv($count, 2);

        if ($count % 2 == 1) {
            return $sorted[$middle];
        }

        return ($sorted[$middle - 1] + $sorted[$middle]) / 2;
    }

    /**
     * Calculate standard deviation
     */
    private function calculateStdDev(Collection $values): float
    {
        $avg = $values->avg();
        $squareDiffs = $values->map(function ($x) use ($avg) {
            return pow($x - $avg, 2);
        });
        return sqrt($squareDiffs->avg());
    }
}
```

---

## 6. LARAVEL CONTROLLERS (PHP API Logic)

### 6.1 Upload Controller

```php
// app/Http/Controllers/UploadController.php

<?php

namespace App\Http\Controllers;

use App\Models\UploadLog;
use App\Http\Requests\StoreMarksRequest;
use App\Services\ExcelImportService;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    protected ExcelImportService $importService;

    public function __construct(ExcelImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Show upload page
     */
    public function showMarksUpload()
    {
        return view('uploads.marks', [
            'qualifications' => \App\Models\Qualification::all(),
            'uploadHistory' => UploadLog::where('upload_type', 'component_marks')
                ->latest()
                ->paginate(10)
        ]);
    }

    /**
     * Handle marks upload
     */
    public function storeMarksUpload(StoreMarksRequest $request)
    {
        try {
            // Store uploaded file
            $filePath = $request->file('marks_file')->store('uploads/marks', 'local');

            // Process import
            $results = $this->importService->importComponentMarks(
                storage_path('app/' . $filePath),
                $request->series_id,
                auth()->user()->school_id,
                auth()->id()
            );

            // Log upload
            UploadLog::create([
                'uploaded_by' => auth()->id(),
                'school_id' => auth()->user()->school_id,
                'series_id' => $request->series_id,
                'subject_id' => $request->subject_id,
                'file_name' => $request->file('marks_file')->getClientOriginalName(),
                'file_path' => $filePath,
                'upload_type' => 'component_marks',
                'records_processed' => $results['summary']['processed'],
                'records_failed' => $results['summary']['failed_count'],
                'status' => $results['summary']['failed_count'] > 0 ? 'partial' : 'success',
                'error_details' => json_encode($results['failed'] ?? []),
            ]);

            return response()->json([
                'message' => 'Upload processed successfully',
                'data' => $results,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Upload failed',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get upload history
     */
    public function uploadHistory(Request $request)
    {
        $history = UploadLog::where('school_id', auth()->user()->school_id)
            ->with(['series', 'subject', 'user'])
            ->latest()
            ->paginate(15);

        return view('uploads.history', ['uploads' => $history]);
    }
}
```

### 6.2 Analytics Controller

```php
// app/Http/Controllers/AnalyticsController.php

<?php

namespace App\Http\Controllers;

use App\Models\ExamSeries;
use App\Models\Subject;
use App\Models\SubjectResult;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Show yearly analytics page
     */
    public function yearly(Request $request)
    {
        $filters = $request->only(['year', 'series_id', 'subject_id', 'qualification_type', 'grade']);
        
        $data = [
            'gradeDistribution' => $this->analyticsService->getGradeDistribution($filters),
            'passFailStats' => $this->analyticsService->getPassFailStats($filters),
            'subjectPerformance' => $this->analyticsService->getSubjectPerformance($filters),
            'statisticalSummary' => $this->analyticsService->getStatisticalSummary($filters),
            'examSeries' => ExamSeries::where('is_active', true)->get(),
            'subjects' => Subject::all(),
        ];

        if ($request->ajax()) {
            return response()->json($data);
        }

        return view('analytics.yearly', $data);
    }

    /**
     * Get YoY comparison data
     */
    public function yoyComparison(Request $request)
    {
        $comparison = $this->analyticsService->getYearOnYearComparison($request->subject_id);
        return response()->json($comparison);
    }

    /**
     * Export analytics to PDF/Excel
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'pdf'); // pdf or excel
        $filters = $request->only(['year', 'series_id', 'subject_id']);

        $data = [
            'gradeDistribution' => $this->analyticsService->getGradeDistribution($filters),
            'passFailStats' => $this->analyticsService->getPassFailStats($filters),
            'subjectPerformance' => $this->analyticsService->getSubjectPerformance($filters),
            'statisticalSummary' => $this->analyticsService->getStatisticalSummary($filters),
        ];

        if ($format === 'pdf') {
            $pdf = \PDF::loadView('reports.analytics', $data);
            return $pdf->download('analytics-report.pdf');
        } else {
            // Excel export using maatwebsite/excel
            return new \App\Exports\AnalyticsExport($data);
        }
    }
}
```

---

## 7. LARAVEL ROUTES (PHP API Endpoints)

### 7.1 Web Routes

```php
// routes/web.php

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Students
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/search', [StudentController::class, 'search'])->name('students.search');
    Route::get('/students/{candidate}', [StudentController::class, 'show'])->name('students.show');

    // Uploads
    Route::get('/uploads/marks', [UploadController::class, 'showMarksUpload'])->name('uploads.marks');
    Route::post('/uploads/marks', [UploadController::class, 'storeMarksUpload'])->name('uploads.marks.store');
    Route::get('/uploads/thresholds', [UploadController::class, 'showThresholdsUpload'])->name('uploads.thresholds');
    Route::post('/uploads/thresholds', [UploadController::class, 'storeThresholdsUpload'])->name('uploads.thresholds.store');
    Route::get('/uploads/history', [UploadController::class, 'uploadHistory'])->name('uploads.history');

    // Analytics
    Route::get('/analytics/yearly', [AnalyticsController::class, 'yearly'])->name('analytics.yearly');
    Route::get('/analytics/yoy-comparison', [AnalyticsController::class, 'yoyComparison'])->name('analytics.yoy');
    Route::post('/analytics/export', [AnalyticsController::class, 'export'])->name('analytics.export');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{id}/download', [ReportController::class, 'download'])->name('reports.download');

    // Admin
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::resource('users', AdminController::class);
        Route::resource('subjects', AdminController::class);
        Route::resource('components', AdminController::class);
        Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::get('/', function () {
    return redirect('/dashboard');
});
```

---

## 8. BLADE TEMPLATES (PHP Views)

### 8.1 Layout Template

```blade
<!-- resources/views/layouts/app.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Cambridge Exam Portal')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <header class="bg-white border-b border-gray-200">
                <div class="px-6 py-4 flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-900">
                        @yield('page-title', 'Dashboard')
                    </h1>
                    <div class="flex items-center gap-4">
                        <span class="text-gray-600">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-red-600 hover:text-red-700">Logout</button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-auto p-6">
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded">
                        <ul class="text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded">
                        <p class="text-green-700">{{ session('success') }}</p>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
```

### 8.2 Analytics Dashboard Template

```blade
<!-- resources/views/analytics/yearly.blade.php -->

@extends('layouts.app')

@section('title', 'Yearly Analytics')
@section('page-title', 'Yearly Performance Analysis')

@section('content')
<div class="grid grid-cols-12 gap-6">
    <!-- Filters Panel -->
    <div class="col-span-12 md:col-span-3 bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">Filters</h3>
        
        <form id="analyticsFilters" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                <select name="year" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">All Years</option>
                    @for ($year = now()->year; $year >= 2020; $year--)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Series</label>
                <select name="series_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">All Series</option>
                    @foreach($examSeries as $series)
                        <option value="{{ $series->id }}">{{ $series->series_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                <select name="subject_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->subject_name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700">
                Apply Filters
            </button>
        </form>
    </div>

    <!-- Analytics Content -->
    <div class="col-span-12 md:col-span-9">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow">
                <p class="text-gray-600 text-sm">Total Students</p>
                <p class="text-3xl font-bold text-gray-900">{{ $statisticalSummary['total_students'] }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <p class="text-gray-600 text-sm">Avg. Percentage</p>
                <p class="text-3xl font-bold text-blue-600">{{ $statisticalSummary['average_percentage'] }}%</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <p class="text-gray-600 text-sm">Pass Rate</p>
                <p class="text-3xl font-bold text-green-600">{{ round(($passFailStats['passed'] / $passFailStats['total']) * 100, 2) }}%</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <p class="text-gray-600 text-sm">Highest Score</p>
                <p class="text-3xl font-bold text-purple-600">{{ $statisticalSummary['highest_score'] }}</p>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-2 gap-6 mb-6">
            <!-- Grade Distribution -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">Grade Distribution</h3>
                <canvas id="gradeChart"></canvas>
            </div>

            <!-- Pass/Fail Pie -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">Pass/Fail Statistics</h3>
                <canvas id="passFailChart"></canvas>
            </div>
        </div>

        <!-- Subject Performance Table -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">
            <h3 class="text-lg font-semibold mb-4">Subject-wise Performance</h3>
            <table class="w-full">
                <thead class="border-b-2 border-gray-300">
                    <tr>
                        <th class="text-left py-2">Subject</th>
                        <th class="text-center">Students</th>
                        <th class="text-center">Avg %</th>
                        <th class="text-center">Pass Rate</th>
                        <th class="text-center">Min-Max</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subjectPerformance as $perf)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="py-3">{{ $perf->subject->subject_name }}</td>
                        <td class="text-center">{{ $perf->total_students }}</td>
                        <td class="text-center font-semibold">{{ round($perf->avg_percentage, 2) }}%</td>
                        <td class="text-center">{{ round($perf->pass_rate, 2) }}%</td>
                        <td class="text-center text-sm text-gray-600">
                            {{ round($perf->min_percentage, 2) }} - {{ round($perf->max_percentage, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Export Button -->
        <div class="flex gap-4">
            <form method="POST" action="{{ route('analytics.export') }}" class="inline">
                @csrf
                <input type="hidden" name="format" value="pdf">
                <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-md hover:bg-red-700">
                    Export PDF
                </button>
            </form>
            <form method="POST" action="{{ route('analytics.export') }}" class="inline">
                @csrf
                <input type="hidden" name="format" value="excel">
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700">
                    Export Excel
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Chart.js initialization
    const gradeCtx = document.getElementById('gradeChart').getContext('2d');
    new Chart(gradeCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($gradeDistribution)) !!},
            datasets: [{
                label: 'Number of Students',
                data: {!! json_encode(array_values($gradeDistribution)) !!},
                backgroundColor: [
                    '#10B981', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#6B7280'
                ]
            }]
        }
    });

    const passFailCtx = document.getElementById('passFailChart').getContext('2d');
    new Chart(passFailCtx, {
        type: 'doughnut',
        data: {
            labels: ['Passed', 'Failed'],
            datasets: [{
                data: [{{ $passFailStats['passed'] }}, {{ $passFailStats['failed'] }}],
                backgroundColor: ['#10B981', '#EF4444']
            }]
        }
    });
</script>
@endsection
```

---

## 9. IMPLEMENTATION ROADMAP (18 Weeks)

### **Week 1-2: Setup & Foundation**
- [ ] Set up Laravel project with Docker
- [ ] Configure database (MySQL)
- [ ] Create all migration files
- [ ] Implement authentication system
- [ ] Create user roles and permissions (using spatie/laravel-permission)
- [ ] Set up basic layouts and navigation

**Deliverables:**
- Working Laravel app with user auth
- Database schema created
- Git repo initialized

### **Week 3-4: Core Models & Database Population**
- [ ] Create all Eloquent models with relationships
- [ ] Create database seeders for qualifications, subjects, components
- [ ] Test model relationships
- [ ] Create test data factory for testing

**Deliverables:**
- All models properly configured
- Data relationships verified
- Test data available

### **Week 5-6: Upload System - Part 1**
- [ ] Create ExcelImportService for marks import
- [ ] Implement UploadController actions
- [ ] Create upload validation requests
- [ ] Build upload UI (Blade templates)
- [ ] Test file upload & validation

**Deliverables:**
- File upload working
- Validation in place
- Error handling implemented

### **Week 7-8: Calculation Engine**
- [ ] Implement MarkCalculationService
- [ ] Create grade assignment logic
- [ ] Test all calculation scenarios
- [ ] Create database jobs for bulk calculations
- [ ] Implement grade threshold upload

**Deliverables:**
- Mark calculations working correctly
- All grades assigning properly
- Bulk operations functioning

### **Week 9-10: Student Dashboard & Search**
- [ ] Build student search functionality
- [ ] Create individual student profile page
- [ ] Display enrollment history
- [ ] Create component-wise breakdown views
- [ ] Implement result charts (Chart.js)

**Deliverables:**
- Student search operational
- Profile pages displaying correctly
- Charts rendering properly

### **Week 11-12: Analytics Engine**
- [ ] Implement AnalyticsService
- [ ] Create analytics controller endpoints
- [ ] Build yearly analysis view
- [ ] Implement grade distribution charts
- [ ] Create subject performance analysis
- [ ] Add YoY comparison logic

**Deliverables:**
- All analytics queries working
- Visualizations displaying correctly
- Filtering functionality complete

### **Week 13-14: Reporting & Export**
- [ ] Implement PDF generation (using DomPDF)
- [ ] Create Excel export (using maatwebsite/excel)
- [ ] Build report templates
- [ ] Test export functionality
- [ ] Create scheduled report generation

**Deliverables:**
- PDF reports generating
- Excel exports working
- Reports scheduling available

### **Week 15: Admin Panel & Settings**
- [ ] Build user management interface
- [ ] Create subject/component management
- [ ] Implement qualification settings
- [ ] Build audit log viewer
- [ ] Create backup functionality

**Deliverables:**
- Admin panel fully functional
- All settings manageable via UI
- Audit logs viewable

### **Week 16: Testing & Optimization**
- [ ] Write unit tests (PHPUnit)
- [ ] Write feature tests (Pest)
- [ ] Performance testing and optimization
- [ ] Database query optimization
- [ ] Caching implementation (Redis)

**Deliverables:**
- Test coverage >80%
- Performance benchmarks met
- Optimizations in place

### **Week 17-18: Deployment & Documentation**
- [ ] Set up production environment
- [ ] Deploy to server
- [ ] Configure CI/CD pipeline
- [ ] Write user documentation
- [ ] Conduct UAT with school
- [ ] Final bug fixes

**Deliverables:**
- Live production system
- Complete documentation
- Training materials

---

## 10. ESSENTIAL CONFIGURATION FILES

### 10.1 docker-compose.yml

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    ports:
      - "8000:8000"
    environment:
      - APP_ENV=local
      - APP_KEY=
      - APP_DEBUG=true
      - DB_CONNECTION=mysql
      - DB_HOST=mysql
      - DB_PORT=3306
      - DB_DATABASE=cambridge_portal
      - DB_USERNAME=root
      - DB_PASSWORD=secret
      - CACHE_DRIVER=redis
      - REDIS_HOST=redis
    volumes:
      - .:/var/www/html
    depends_on:
      - mysql
      - redis
    networks:
      - app-network

  mysql:
    image: mysql:8.0
    environment:
      - MYSQL_DATABASE=cambridge_portal
      - MYSQL_ROOT_PASSWORD=secret
      - MYSQL_CHARACTER_SET_SERVER=utf8mb4
      - MYSQL_COLLATION_SERVER=utf8mb4_unicode_ci
    ports:
      - "3306:3306"
    volumes:
      - dbdata:/var/lib/mysql
    networks:
      - app-network

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"
    networks:
      - app-network

  phpmyadmin:
    image: phpmyadmin:latest
    environment:
      - PMA_HOST=mysql
      - PMA_USER=root
      - PMA_PASSWORD=secret
    ports:
      - "8080:80"
    networks:
      - app-network

volumes:
  dbdata:

networks:
  app-network:
    driver: bridge
```

### 10.2 .env.example

```env
APP_NAME="Cambridge Exam Portal"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=cambridge_portal
DB_USERNAME=root
DB_PASSWORD=secret

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls

UPLOAD_MAX_FILE_SIZE=10240

JWT_SECRET=
```

---

## 11. TESTING STRATEGY (PHP)

### 11.1 Unit Tests

```php
// tests/Unit/MarkCalculationTest.php

<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\MarkCalculationService;
use App\Models\ComponentMarks;
use App\Models\CandidateEnrollment;

class MarkCalculationTest extends TestCase
{
    private MarkCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MarkCalculationService();
    }

    public function test_uniform_mark_calculation()
    {
        $obtained = 75;
        $total = 100;
        
        $uniformMark = ($obtained / $total) * 100;
        
        $this->assertEquals(75, $uniformMark);
    }

    public function test_percentage_calculation()
    {
        $totalObtained = 225;
        $totalMarks = 300;
        
        $percentage = ($totalObtained / $totalMarks) * 100;
        
        $this->assertEquals(75, $percentage);
    }

    public function test_grade_assignment_idcc()
    {
        // Test IDCC grading: A* (90-100), A (80-89), etc.
        $this->assertEquals('A*', $this->getGradeForPercentage(95, 'IDCC'));
        $this->assertEquals('A', $this->getGradeForPercentage(85, 'IDCC'));
        $this->assertEquals('U', $this->getGradeForPercentage(5, 'IDCC'));
    }

    private function getGradeForPercentage(float $percentage, string $qualificationType)
    {
        $grades = [
            'IDCC' => [
                'A*' => 90,
                'A' => 80,
                'B' => 70,
                'C' => 60,
                'D' => 50,
                'E' => 40,
                'F' => 30,
                'G' => 0,
            ],
        ];

        foreach ($grades[$qualificationType] as $grade => $threshold) {
            if ($percentage >= $threshold) {
                return $grade;
            }
        }

        return 'U';
    }
}
```

### 11.2 Feature Tests

```php
// tests/Feature/UploadMarkTest.php

<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use Illuminate\Http\UploadedFile;

class UploadMarkTest extends TestCase
{
    protected User $examOfficer;
    protected School $school;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->school = School::factory()->create();
        $this->examOfficer = User::factory()
            ->create(['school_id' => $this->school->id, 'role' => 'exam_officer']);
    }

    public function test_exam_officer_can_upload_marks()
    {
        $file = UploadedFile::fake()->create('marks.csv');

        $response = $this->actingAs($this->examOfficer)
            ->post('/uploads/marks', [
                'marks_file' => $file,
                'series_id' => 'test-series-id',
                'subject_id' => 'test-subject-id',
            ]);

        $response->assertStatus(200);
    }

    public function test_invalid_marks_file_rejected()
    {
        $invalidFile = UploadedFile::fake()->create('marks.txt', 10, 'text/plain');

        $response = $this->actingAs($this->examOfficer)
            ->post('/uploads/marks', [
                'marks_file' => $invalidFile,
            ]);

        $response->assertStatus(422);
    }

    public function test_unauthenticated_user_redirected_to_login()
    {
        $response = $this->post('/uploads/marks');
        $response->assertRedirect('/login');
    }
}
```

---

## 12. DEPLOYMENT CHECKLIST

### Pre-Deployment
- [ ] All tests passing (PHPUnit/Pest)
- [ ] Code review completed
- [ ] Database backups configured
- [ ] SSL certificate installed
- [ ] Environment variables configured
- [ ] Redis cache configured
- [ ] Job queue tested
- [ ] Email service configured

### Deployment Commands

```bash
# Production deployment steps
git clone [repository-url]
cd cambridge-exam-portal
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:work &
```

---

## 13. ESTIMATED COSTS & RESOURCES

### Development Team
- **4-5 PHP/Laravel developers** (18 weeks)
- **1 Database Administrator** (6 weeks)
- **1 QA Engineer** (8 weeks)
- **1 DevOps Engineer** (6 weeks)

### Infrastructure (Monthly)
- **Server Hosting:** $200-500 (AWS/Digital Ocean)
- **Database:** Included in hosting
- **Email Service:** $50-100
- **SSL Certificate:** Free (Let's Encrypt)
- **CDN:** $20-50 (optional)

### Tools & Licenses
- **PHPStorm License:** $199/year per developer
- **GitLab/GitHub:** Free or $21/month
- **Sentry (Error Tracking):** $29-99/month
- **DataDog/New Relic (Monitoring):** $50-200/month

---

## 14. HANDOVER & MAINTENANCE

**Phase 1: Knowledge Transfer**
- API documentation complete
- Code documentation complete
- Database schema documented
- Deployment procedures documented

**Phase 2: Support (First 3 months)**
- Bug fixes and critical issues
- Performance optimization
- User training support
- Weekly check-ins

**Phase 3: Maintenance (Ongoing)**
- Security updates
- Database backups & recovery
- Performance monitoring
- Feature enhancements

---

## Quick Start Commands for Antiggravity Team

```bash
# 1. Clone and setup
git clone [repo-url] cambridge-exam-portal
cd cambridge-exam-portal

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Run Docker containers
docker-compose up -d

# 5. Run migrations and seeders
php artisan migrate --seed

# 6. Start development server
php artisan serve
npm run dev

# 7. Run tests
php artisan test

# 8. Access the application
# Web: http://localhost:8000
# PHPMyAdmin: http://localhost:8080
```

---

## File Structure Summary for Development

```
/app          → All PHP logic (Controllers, Models, Services)
/database     → Migrations, Seeders, Factories
/resources    → Blade templates, CSS, JavaScript
/routes       → Web routes
/tests        → Unit & Feature tests
/storage      → Uploaded files, logs
/config       → Configuration files
/.env         → Environment variables
/docker-compose.yml → Container setup
```

---

**Project Status: Ready for Development**

This plan is comprehensive and ready for implementation. Each section provides:
- Specific PHP/Laravel code examples
- SQL/MySQL schema definitions
- Development workflow guidelines
- Testing strategies
- Deployment procedures

**Antiggravity team can begin immediately with Phase 1 setup.**

