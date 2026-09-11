<?php
/**
 * fix_all_grades_from_excel.php
 *
 * Step 1 — Fix 58 grade mismatches (DB ← Excel, source of truth)
 * Step 2 — Add 15 missing candidates
 * Step 3 — Bulk-import 834 results that are missing from the DB
 *
 * Run: php fix_all_grades_from_excel.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ExamSeries;
use App\Models\Subject;
use App\Models\Candidate;
use App\Models\CandidateEnrollment;
use App\Models\SubjectResult;
use App\Models\School;
use App\Models\Qualification;

// ─── Constants ────────────────────────────────────────────────────────────────
define('RESULTS_FOLDER', 'D:\\Rashya Sharma\\CIE\\Other Docs\\CIE ALL Broadsheets\\Results file');

const SUBJECT_NAME_TO_CODE = [
    'ACCOUNTING'                        => ['9706', '0452'],
    'ART AND DESIGN'                    => ['9479', '0400'],
    'BIOLOGY'                           => ['9700', '0610'],
    'BUSINESS'                          => ['9609', '0450'],
    'BUSINESS STUDIES'                  => ['9609', '0450'],
    'CHEMISTRY'                         => ['9701', '0620'],
    'CO-ORD SCIENCES (DOUBLE AWARD)'    => ['0654'],
    'COMBINED SCIENCE'                  => ['0653'],
    'COMPUTER SCIENCE'                  => ['9618', '9608', '0478'],
    'ECONOMICS'                         => ['9708', '0455'],
    'ENGLISH AS A SECOND LANGUAGE'      => ['0510'],
    'ENGLISH GENERAL PAPER'             => ['8021'],
    'FOREIGN LANGUAGE FRENCH'           => ['0520'],
    'FURTHER MATHEMATICS'               => ['9231'],
    'HINDI AS A SECOND LANGUAGE'        => ['0549'],
    'HISTORY'                           => ['9489', '9389'],
    'INFORMATION AND COMMUNICATION'     => ['0417'],
    'INFORMATION TECHNOLOGY'            => ['9626'],
    'LITERATURE IN ENGLISH'             => ['9695', '0475'],
    'MATHEMATICS'                       => ['9709', '0580'],
    'MATHEMATICS (W/OUT COURSEWORK)'    => ['9709', '0580'],
    'PHYSICS'                           => ['9702', '0625'],
    'PSYCHOLOGY'                        => ['9990'],
    'SOCIOLOGY'                         => ['9699'],
];

// Double-letter grades (AA, BB …) are LEGITIMATE and stored as-is.
// Exception: Co-ordinated Sciences uses AA/BB/CC/DD/EE/FF but U stays as U (not UU).
const COORDINATED_SCIENCE_COLS = [
    'CO-ORD SCIENCES (DOUBLE AWARD)',
    'COORDINATED SCIENCES',
    'CO-ORDINATED SCIENCES',
];

function normaliseGrade(string $raw, string $colName = ''): string
{
    $g = strtoupper(trim(str_replace('^', '', $raw)));
    if (str_contains($raw, '*')) return 'A*';

    // Co-ordinated Sciences: UU is not a valid grade — treat as U
    if (in_array(strtoupper(trim($colName)), COORDINATED_SCIENCE_COLS) && $g === 'UU') {
        return 'U';
    }

    return $g;
}

function parseSeriesFromFilename(string $filename): ?array
{
    if (preg_match('/(June|March|November|October|January)\s+(\d{4})/i', $filename, $m)) {
        return ['month' => ucfirst(strtolower($m[1])), 'year' => (int)$m[2]];
    }
    return null;
}

// ─── Bootstrap lookups ────────────────────────────────────────────────────────
$allSeries    = ExamSeries::all()->keyBy(fn($s) => $s->year . '_' . $s->month);
$subjectByCode = Subject::all()->keyBy('subject_code');
$school        = School::first();
$admin         = \App\Models\User::first();

// Get or create default qualification
$defaultQual = Qualification::where('qualification_type', 'AS_A_LEVEL')->first()
    ?? Qualification::first();

// ─── Counters ─────────────────────────────────────────────────────────────────
$fixed       = 0;
$created     = 0;   // new SubjectResult records
$candCreated = 0;
$skipped     = 0;
$errors      = [];

// ─── Process each file ────────────────────────────────────────────────────────
$files = glob(RESULTS_FOLDER . DIRECTORY_SEPARATOR . '*.xls*');
sort($files);

foreach ($files as $filePath) {
    $basename = basename($filePath);
    $parsed   = parseSeriesFromFilename($basename);
    if (!$parsed) { echo "[SKIP] No series in: $basename\n"; continue; }

    $seriesKey = $parsed['year'] . '_' . $parsed['month'];
    $series    = $allSeries[$seriesKey] ?? null;
    if (!$series) { echo "[SKIP] Series not in DB: {$parsed['year']} {$parsed['month']}\n"; continue; }

    // Read spreadsheet
    try {
        $reader      = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $rows        = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
    } catch (\Throwable $e) {
        echo "[SKIP] Cannot read $basename: " . $e->getMessage() . "\n";
        continue;
    }

    // Find header row
    $headerRowIdx = null;
    foreach ($rows as $idx => $row) {
        foreach ($row as $cell) {
            if ($cell && stripos((string)$cell, 'cand') !== false) {
                $headerRowIdx = $idx;
                break 2;
            }
        }
    }
    if ($headerRowIdx === null) { echo "[SKIP] No header in $basename\n"; continue; }

    $headerRow  = array_values($rows[$headerRowIdx]);
    $subjectCols = [];
    for ($c = 2; $c < count($headerRow); $c++) {
        $colName = strtoupper(trim((string)($headerRow[$c] ?? '')));
        if (!$colName) continue;
        $subjectCols[$c] = ['name' => $colName, 'codes' => SUBJECT_NAME_TO_CODE[$colName] ?? null];
    }

    echo "\n── {$series->year} {$series->month}  ($basename)\n";

    $usedEnrollments = [];

    foreach ($rows as $idx => $row) {
        if ($idx <= $headerRowIdx) continue;
        $row = array_values($row);

        $rawCandNo = trim((string)($row[0] ?? ''));
        $candName  = trim((string)($row[1] ?? ''));
        if (!$rawCandNo || !$candName || !is_numeric($rawCandNo)) continue;

        $candNo = str_pad((int)$rawCandNo, 4, '0', STR_PAD_LEFT);

        // Find or CREATE candidate
        $candidate = Candidate::where('school_id', $school?->id)
            ->where('candidate_number', $candNo)
            ->first()
            ?? Candidate::where('school_id', $school?->id)
                ->where('candidate_number', (string)(int)$rawCandNo)
                ->first();

        if (!$candidate) {
            try {
                $candidate = Candidate::create([
                    'school_id'        => $school?->id,
                    'candidate_number' => $candNo,
                    'candidate_name'   => $candName,
                    'enrollment_date'  => "{$series->year}-01-01",
                    'status'           => 'active',
                ]);
                $candCreated++;
                echo "   [+CAND] $candNo $candName\n";
            } catch (\Throwable $e) {
                $errors[] = "CAND $candNo $candName: " . $e->getMessage();
                continue;
            }
        }

        if (!isset($usedEnrollments[$candidate->id])) {
            $usedEnrollments[$candidate->id] = [];
        }

        foreach ($subjectCols as $c => $colInfo) {
            $rawGrade = trim((string)($row[$c] ?? ''));
            if (!$rawGrade) continue;

            $excelGrade = normaliseGrade($rawGrade, $colInfo['name']);
            $codes      = $colInfo['codes'];

            if (!$codes) { $skipped++; continue; }

            // Try each code to find matching subject & enrollment
            $subject    = null;
            $enrollment = null;

            foreach ($codes as $code) {
                $subj = $subjectByCode[$code] ?? null;
                if (!$subj) continue;

                $enr = CandidateEnrollment::where('candidate_id', $candidate->id)
                    ->where('series_id', $series->id)
                    ->where('subject_id', $subj->id)
                    ->first();

                if ($enr && !in_array($enr->id, $usedEnrollments[$candidate->id])) {
                    $subject    = $subj;
                    $enrollment = $enr;
                    $usedEnrollments[$candidate->id][] = $enr->id;
                    break;
                }
            }

            // If no enrollment found, create one using the first valid subject code
            // that the candidate is NOT already enrolled in
            if (!$enrollment) {
                foreach ($codes as $code) {
                    $subj = $subjectByCode[$code] ?? null;
                    if ($subj) {
                        $exists = CandidateEnrollment::where('candidate_id', $candidate->id)
                            ->where('series_id', $series->id)
                            ->where('subject_id', $subj->id)
                            ->exists();
                        if (!$exists) {
                            $subject = $subj;
                            break;
                        }
                    }
                }

                // Fallback to first if all somehow exist
                if (!$subject) {
                    foreach ($codes as $code) {
                        $subj = $subjectByCode[$code] ?? null;
                        if ($subj) { $subject = $subj; break; }
                    }
                }

                if (!$subject) { $skipped++; continue; }

                try {
                    $enrollment = CandidateEnrollment::create([
                        'candidate_id'      => $candidate->id,
                        'series_id'         => $series->id,
                        'subject_id'        => $subject->id,
                        'qualification_id'  => $subject->qualification_id ?? $defaultQual->id,
                        'enrollment_status' => 'enrolled',
                        'enrolled_date'     => "{$series->year}-01-01",
                    ]);
                    $usedEnrollments[$candidate->id][] = $enrollment->id;
                } catch (\Throwable $e) {
                    $errors[] = "ENROLL {$candidate->candidate_name} {$subject->subject_code}: " . $e->getMessage();
                    continue;
                }
            }

            // Upsert the SubjectResult
            try {
                $existing = SubjectResult::where('enrollment_id', $enrollment->id)->first();
                $oldGrade = $existing ? strtoupper(trim($existing->grade ?? '')) : null;

                if ($existing) {
                    // Update grade (and is_passed) but preserve existing pum/marks
                    $existing->grade     = $excelGrade;
                    $existing->is_passed = !in_array($excelGrade, ['U', 'X', 'Q', 'F']);
                    $existing->status    = 'complete';
                    $existing->save();
                } else {
                    // New record — no marks data from Excel, default pum to empty string
                    SubjectResult::create([
                        'enrollment_id' => $enrollment->id,
                        'subject_id'    => $subject->id,
                        'series_id'     => $series->id,
                        'grade'         => $excelGrade,
                        'pum'           => '',
                        'is_passed'     => !in_array($excelGrade, ['U', 'X', 'Q', 'F']),
                        'status'        => 'complete',
                        'uploaded_by'   => $admin?->id,
                    ]);
                }

                if ($oldGrade === null) {
                    $created++;
                } elseif ($oldGrade !== $excelGrade) {
                    $fixed++;
                    echo "   [FIX] {$candNo} {$candName} | {$colInfo['name']} | {$oldGrade} → {$excelGrade}\n";
                }
            } catch (\Throwable $e) {
                $errors[] = "RESULT {$candidate->candidate_name} {$subject->subject_code}: " . $e->getMessage();
            }
        }
    }
}

// ─── Final Summary ────────────────────────────────────────────────────────────
echo "\n\n";
echo "╔══════════════════════════════════╗\n";
echo "║         FIX SUMMARY              ║\n";
echo "╠══════════════════════════════════╣\n";
printf("║  ✅ Grades fixed      : %6d   ║\n", $fixed);
printf("║  ➕ New results added  : %6d   ║\n", $created);
printf("║  👤 New candidates     : %6d   ║\n", $candCreated);
printf("║  ⏭  Skipped (no map)  : %6d   ║\n", $skipped);
printf("║  ❌ Errors             : %6d   ║\n", count($errors));
echo "╚══════════════════════════════════╝\n";

if ($errors) {
    echo "\n--- ERRORS ---\n";
    foreach ($errors as $e) echo "  $e\n";
}
