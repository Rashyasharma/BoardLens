<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// =========================================================
// SOURCE DATA from June 2026 marksheet (from screenshots)
// Grade format: lowercase with ^ = AS level resit notation
// =========================================================

// GCE AS & A Level results from marksheet
$aLevelData = [
    // cand_no => [subject_code => grade]
    '8'    => ['9701'=>'e', '9702'=>'c'],
    '4001' => ['9479'=>'d', '9609'=>'c', '9702'=>'c'],
    '4002' => ['9479'=>'U', '9709'=>'d'],
    '4003' => ['9706'=>'e', '9708'=>'A*', '9709'=>'a'],
    '4004' => ['9479'=>'d', '9609'=>'e', '9709'=>'d'],
    '4005' => ['9609'=>'e', '9709'=>'d', '9702'=>'b'],
    '4006' => ['9609'=>'U', '9701'=>'e', '9626'=>'c', '9702'=>'e'],
    '4007' => ['9609'=>'U', '9708'=>'U', '9626'=>'e'],
    '4008' => ['9479'=>'b', '9609'=>'c', '9709'=>'a'],
    '4009' => ['9479'=>'e', '9609'=>'U', '9709'=>'e'],
    '4010' => ['9701'=>'e', '9608'=>'d', '9626'=>'d', '9709'=>'d'],
    '4011' => ['9609'=>'d', '9608'=>'d', '9626'=>'c', '9709'=>'c'],
    '4012' => ['9700'=>'d', '9608'=>'d', '8021'=>'U'],
    '4013' => ['9479'=>'e', '9609'=>'a', '9708'=>'U'],
    '4015' => ['9706'=>'d', '9708'=>'c', '9626'=>'d', '9709'=>'c'],
    '4016' => ['9609'=>'e', '8021'=>'U'],
    '4018' => ['9706'=>'e', '9609'=>'e', '9709'=>'U'],
    '4019' => ['9479'=>'U', '9708'=>'U', '9709'=>'U'],
    '4020' => ['9706'=>'d', '9608'=>'d', '9626'=>'a', '9990'=>'d'],
    '4022' => ['9706'=>'U', '9708'=>'U', '9709'=>'U'],
    '4023' => ['9706'=>'U', '9609'=>'c', '9708'=>'U', '9626'=>'e'],
    '5016' => ['9609'=>'D', '9709'=>'B'],
    '5017' => ['9479'=>'e', '9702'=>'e'],
    '5018' => ['9706'=>'E', '9709'=>'D'],
    '5020' => ['9709'=>'U'],
    '5021' => ['9701'=>'E', '9709'=>'E'],
    '5022' => ['9709'=>'U'],
    '5023' => ['9701'=>'C', '9708'=>'b', '9709'=>'B'],
    '5024' => ['9609'=>'D', '9709'=>'E'],
    '5029' => ['9706'=>'U', '9609'=>'E'],
];

// IGCSE results from marksheet
$igcseData = [
    '4013' => ['0471'=>'B'],
    '5005' => ['0580'=>'D'],
    '5014' => ['0455'=>'F', '0417'=>'F'],
];

// Subject code to DB subject_name mapping for reference
$subjectMap = [
    '9706'=>'Accounting','9479'=>'Art And Design','9700'=>'Biology',
    '9609'=>'Business','9701'=>'Chemistry','9608'=>'Computer Science',
    '9618'=>'Computer Science','9708'=>'Economics','8021'=>'English General Paper',
    '9231'=>'Further Mathematics','9626'=>'Information Technology',
    '9709'=>'Mathematics','9702'=>'Physics','9990'=>'Psychology',
    '9093'=>'English Language','9695'=>'Literature In English',
    '0471'=>'Travel And Tourism','0580'=>'Mathematics',
    '0455'=>'Economics','0417'=>'Information And Communication',
];

// Get June 2026 series
$june2026 = DB::table('exam_series')
    ->where('year', 2026)->where('month','LIKE','%June%')->first();
if (!$june2026) { echo "ERROR: June 2026 series not found!\n"; exit; }
echo "Series: {$june2026->series_name} (id: {$june2026->id})\n\n";

// Get qualifications
$igcseQual  = DB::table('qualifications')->where('qualification_name','LIKE','%IGCSE%')->first();
$aLevelQual = DB::table('qualifications')->where('qualification_name','LIKE','%AS%')->orWhere('qualification_name','LIKE','%A Level%')->first();
echo "IGCSE qual: {$igcseQual->qualification_name} ({$igcseQual->id})\n";
echo "AS/A Level qual: {$aLevelQual->qualification_name} ({$aLevelQual->id})\n\n";

// Get all subjects indexed by code
$allSubjects = DB::table('subjects')->get()->keyBy('subject_code');

// School ID (Lucky International School)
$school = DB::table('schools')->where('school_name','LIKE','%Lucky%')->first();
$schoolId = $school ? $school->id : null;
echo "School: " . ($school ? $school->school_name : 'NOT FOUND') . " (id: $schoolId)\n\n";

$issues = [];
$fixed  = [];

function getCandidate($candidateNumber) {
    // Search without school filter since numbers are unique per marksheet
    $existing = DB::table('candidates')
        ->where('candidate_number', $candidateNumber)
        ->first();
    return $existing;
}

function checkAndFix($candidateNumber, $subjectCode, $expectedGrade, $seriesId, $qualId, $schoolId, $allSubjects, &$issues, &$fixed) {
    global $subjectMap;

    $candidate = DB::table('candidates')
        ->where('candidate_number', $candidateNumber)
        ->first();

    if (!$candidate) {
        $issues[] = "CANDIDATE NOT FOUND: #{$candidateNumber}";
        return;
    }

    $subject = $allSubjects[$subjectCode] ?? null;
    if (!$subject) {
        $issues[] = "SUBJECT CODE NOT FOUND: {$subjectCode} for candidate #{$candidateNumber}";
        return;
    }

    $subjectName = $subject->subject_name ?? ($subjectMap[$subjectCode] ?? $subjectCode);

    // Check enrollment
    $enrollment = DB::table('candidate_enrollments')
        ->where('candidate_id', $candidate->id)
        ->where('subject_id', $subject->id)
        ->where('series_id', $seriesId)
        ->first();

    if (!$enrollment) {
        // Need to create enrollment
        $enrollmentId = \Illuminate\Support\Str::uuid()->toString();
        DB::table('candidate_enrollments')->insert([
            'id'               => $enrollmentId,
            'candidate_id'     => $candidate->id,
            'series_id'        => $seriesId,
            'qualification_id' => $qualId,
            'subject_id'       => $subject->id,
            'enrollment_status'=> 'enrolled',
            'enrolled_date'    => '2026-01-01',
            'candidate_number' => $candidateNumber,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
        $fixed[] = "CREATED ENROLLMENT: #{$candidateNumber} {$candidate->candidate_name} - {$subjectCode} ({$subjectName})";
    } else {
        $enrollmentId = $enrollment->id;
    }

    // Check result
    $result = DB::table('subject_results')
        ->where('enrollment_id', $enrollmentId)
        ->first();

    // Normalize grade for comparison (strip ^ suffix)
    $dbGrade = $result ? strtoupper(trim($result->grade)) : null;
    $expectedGradeNorm = strtoupper(trim($expectedGrade));

    if (!$result) {
        // Get uploaded_by from existing result
        $uploadedBy = DB::table('subject_results')->whereNotNull('uploaded_by')->value('uploaded_by') ?? 'system';
        
        DB::table('subject_results')->insert([
            'id'                      => \Illuminate\Support\Str::uuid()->toString(),
            'enrollment_id'           => $enrollmentId,
            'subject_id'              => $subject->id,
            'series_id'               => $seriesId,
            'grade'                   => strtoupper($expectedGrade),
            'pum'                     => 0,
            'total_obtained_marks'    => null,
            'total_marks'             => null,
            'overall_percentage'      => null,
            'calculated_uniform_mark' => null,
            'is_passed'               => !in_array(strtoupper($expectedGrade), ['U','F']) ? 1 : 0,
            'remarks'                 => 'Added from June 2026 marksheet',
            'status'                  => 'complete',
            'result_uploaded_at'      => now(),
            'components_uploaded_at'  => now(),
            'uploaded_by'             => $uploadedBy,
            'created_at'              => now(),
            'updated_at'              => now(),
        ]);
        $fixed[] = "CREATED RESULT:    #{$candidateNumber} {$candidate->candidate_name} - {$subjectCode} ({$subjectName}) = " . strtoupper($expectedGrade);
    } elseif ($dbGrade !== $expectedGradeNorm) {
        // Grade mismatch
        $issues[] = "GRADE MISMATCH: #{$candidateNumber} {$candidate->candidate_name} - {$subjectCode} ({$subjectName}): DB={$dbGrade}, Marksheet={$expectedGradeNorm}";
    }
    // else: all good
}

echo "=== Checking GCE AS & A Level ===\n";
foreach ($aLevelData as $candNum => $subjects) {
    foreach ($subjects as $code => $grade) {
        checkAndFix($candNum, $code, $grade, $june2026->id, $aLevelQual->id, $schoolId, $allSubjects, $issues, $fixed);
    }
}

echo "\n=== Checking IGCSE ===\n";
foreach ($igcseData as $candNum => $subjects) {
    foreach ($subjects as $code => $grade) {
        checkAndFix($candNum, $code, $grade, $june2026->id, $igcseQual->id, $schoolId, $allSubjects, $issues, $fixed);
    }
}

echo "\n========== SUMMARY ==========\n";
echo "\n--- FIXED (" . count($fixed) . ") ---\n";
foreach ($fixed as $f) echo "  ✅ $f\n";

echo "\n--- ISSUES (" . count($issues) . ") ---\n";
foreach ($issues as $i) echo "  ⚠️  $i\n";

if (empty($fixed) && empty($issues)) {
    echo "  All data matches the marksheet!\n";
}
