<?php
/**
 * verify_all_grades.php
 * 
 * Reads all Electronic Results Files from the CIE broadsheets folder,
 * compares each candidate's grade per subject against what is stored in
 * BoardLens, and prints a full verification report.
 *
 * Usage:  php verify_all_grades.php
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
use Illuminate\Support\Facades\DB;

// ─── Configuration ────────────────────────────────────────────────────────────
define('RESULTS_FOLDER', 'D:\\Rashya Sharma\\CIE\\Other Docs\\CIE ALL Broadsheets\\Results file');

// Map Excel subject-name column headers → subject_code in BoardLens DB
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

// Double-letter IGCSE grades (AA, BB …) are LEGITIMATE and stored as-is.
// Exception: Co-ordinated Sciences uses AA/BB/CC/DD/EE/FF but U stays as U (not UU).
const COORDINATED_SCIENCE_COLS_V = [
    'CO-ORD SCIENCES (DOUBLE AWARD)',
    'COORDINATED SCIENCES',
    'CO-ORDINATED SCIENCES',
];

function normaliseGrade(string $raw, string $colName = ''): string
{
    $g = strtoupper(trim(str_replace('^', '', $raw)));
    if (str_contains($raw, '*')) return 'A*';
    if (in_array(strtoupper(trim($colName)), COORDINATED_SCIENCE_COLS_V) && $g === 'UU') {
        return 'U';
    }
    return $g;
}

// Map month word in filename to DB month string
function parseSeriesFromFilename(string $filename): ?array
{
    // "Electronic Results File for June 2022.xls"
    // "Provisional Results File for March 2021.xls"
    if (preg_match('/(June|March|November|October|January)\s+(\d{4})/i', $filename, $m)) {
        return ['month' => ucfirst(strtolower($m[1])), 'year' => (int)$m[2]];
    }
    return null;
}

// ─── Load all series & subjects once ──────────────────────────────────────────
$allSeries   = ExamSeries::all()->keyBy(fn($s) => $s->year . '_' . $s->month);
$allSubjects = Subject::all();

// Build a lookup: code → Subject model
$subjectByCode = $allSubjects->keyBy('subject_code');

// ─── Scan Excel files ──────────────────────────────────────────────────────────
$files = glob(RESULTS_FOLDER . DIRECTORY_SEPARATOR . '*.xls*');
sort($files);

$school = School::first(); // Lucky International School

$totalChecked  = 0;
$totalMissing  = 0;   // in DB but not in Excel (or vice-versa)
$totalMismatch = 0;
$totalMatch    = 0;
$totalSkipped  = 0;   // subject not mapped / series not found

$report = [];

foreach ($files as $filePath) {
    $basename = basename($filePath);

    // Parse series from filename
    $parsed = parseSeriesFromFilename($basename);
    if (!$parsed) {
        echo "[SKIP] Cannot parse series from: $basename\n";
        continue;
    }
    $seriesKey = $parsed['year'] . '_' . $parsed['month'];
    $series    = $allSeries[$seriesKey] ?? null;

    if (!$series) {
        echo "[SKIP] Series not in DB: {$parsed['year']} {$parsed['month']} (file: $basename)\n";
        continue;
    }

    // Read workbook
    try {
        $wb = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        // Fall back to xlrd-style reading via raw COM/PHP — use PhpSpreadsheet
        // Actually: use PhpSpreadsheet which is already a Laravel dependency
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $ws = $spreadsheet->getActiveSheet();
        $rows = $ws->toArray(null, true, true, false);
    } catch (\Throwable $e) {
        echo "[SKIP] Cannot read $basename: " . $e->getMessage() . "\n";
        continue;
    }

    // Find header row (contains "Cand. No" or "Cand")
    $headerRowIdx = null;
    foreach ($rows as $idx => $row) {
        foreach ($row as $cell) {
            if ($cell && stripos((string)$cell, 'cand') !== false) {
                $headerRowIdx = $idx;
                break 2;
            }
        }
    }
    if ($headerRowIdx === null) {
        echo "[SKIP] No header row found in $basename\n";
        continue;
    }

    $headerRow = array_values($rows[$headerRowIdx]);
    // col 0 = Cand No, col 1 = Name, col 2+ = subjects
    $subjectCols = []; // colIndex => [subject_code, ...]
    for ($c = 2; $c < count($headerRow); $c++) {
        $colName = strtoupper(trim((string)($headerRow[$c] ?? '')));
        if (!$colName) continue;
        $codes = SUBJECT_NAME_TO_CODE[$colName] ?? null;
        $subjectCols[$c] = ['name' => $colName, 'codes' => $codes];
    }

    echo "\n══════════════════════════════════════════════\n";
    echo "FILE : $basename\n";
    echo "SERIES: {$series->year} {$series->month} (id={$series->id})\n";
    echo "══════════════════════════════════════════════\n";

    $usedEnrollments = [];

    // Process data rows
    foreach ($rows as $idx => $row) {
        if ($idx <= $headerRowIdx) continue;
        $row = array_values($row);

        $rawCandNo = trim((string)($row[0] ?? ''));
        $candName  = trim((string)($row[1] ?? ''));

        if (!$rawCandNo || !$candName || !is_numeric($rawCandNo)) continue;

        // Candidate number: pad to 4 digits
        $candNo = str_pad((int)$rawCandNo, 4, '0', STR_PAD_LEFT);

        // Find candidate in DB
        $candidate = Candidate::where('school_id', $school?->id)
            ->where('candidate_number', $candNo)
            ->first();

        if (!$candidate) {
            // Also try without leading-zero padding
            $candidate = Candidate::where('school_id', $school?->id)
                ->where('candidate_number', (string)(int)$rawCandNo)
                ->first();
        }

        if ($candidate && !isset($usedEnrollments[$candidate->id])) {
            $usedEnrollments[$candidate->id] = [];
        }

        foreach ($subjectCols as $c => $colInfo) {
            $rawGrade = trim((string)($row[$c] ?? ''));
            if (!$rawGrade) continue; // blank = not enrolled

            $excelGrade = normaliseGrade($rawGrade, $colInfo['name']);
            $colName    = $colInfo['name'];
            $codes      = $colInfo['codes'];

            if (!$codes) {
                $totalSkipped++;
                $report[] = [
                    'status'  => 'NO_MAPPING',
                    'series'  => "{$series->year} {$series->month}",
                    'cand_no' => $candNo,
                    'name'    => $candName,
                    'subject' => $colName,
                    'excel'   => $excelGrade,
                    'db'      => '—',
                    'file'    => $basename,
                ];
                continue;
            }

            // Try each possible code to find a match
            $dbResult  = null;
            $usedCode  = null;

            if ($candidate) {
                foreach ($codes as $code) {
                    $subject = $subjectByCode[$code] ?? null;
                    if (!$subject) continue;

                    $enrollment = CandidateEnrollment::where('candidate_id', $candidate->id)
                        ->where('series_id', $series->id)
                        ->where('subject_id', $subject->id)
                        ->first();

                    if ($enrollment && !in_array($enrollment->id, $usedEnrollments[$candidate->id])) {
                        $dbResult = SubjectResult::where('enrollment_id', $enrollment->id)->first();
                        $usedCode = $code;
                        $usedEnrollments[$candidate->id][] = $enrollment->id;
                        break;
                    }
                }
            }

            $totalChecked++;

            if (!$candidate) {
                $totalMissing++;
                $report[] = [
                    'status'  => 'CANDIDATE_NOT_IN_DB',
                    'series'  => "{$series->year} {$series->month}",
                    'cand_no' => $candNo,
                    'name'    => $candName,
                    'subject' => $colName . ' (' . implode('/', $codes) . ')',
                    'excel'   => $excelGrade,
                    'db'      => '—',
                    'file'    => $basename,
                ];
            } elseif (!$dbResult) {
                $totalMissing++;
                $report[] = [
                    'status'  => 'RESULT_NOT_IN_DB',
                    'series'  => "{$series->year} {$series->month}",
                    'cand_no' => $candNo,
                    'name'    => $candName,
                    'subject' => $colName . ' (' . implode('/', $codes) . ')',
                    'excel'   => $excelGrade,
                    'db'      => '—',
                    'file'    => $basename,
                ];
            } else {
                $dbGrade = strtoupper(trim($dbResult->grade ?? ''));
                if ($dbGrade === $excelGrade) {
                    $totalMatch++;
                    // Uncomment below to see all matched rows too:
                    // $report[] = ['status' => 'OK', ...];
                } else {
                    $totalMismatch++;
                    $report[] = [
                        'status'  => 'GRADE_MISMATCH',
                        'series'  => "{$series->year} {$series->month}",
                        'cand_no' => $candNo,
                        'name'    => $candName,
                        'subject' => $colName . ' (' . $usedCode . ')',
                        'excel'   => $excelGrade,
                        'db'      => $dbGrade,
                        'file'    => $basename,
                    ];
                }
            }
        }
    }
}

// ─── Print Report ──────────────────────────────────────────────────────────────
echo "\n\n";
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║              GRADE VERIFICATION REPORT                  ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

$grouped = [];
foreach ($report as $r) {
    $grouped[$r['status']][] = $r;
}

// GRADE_MISMATCH
if (!empty($grouped['GRADE_MISMATCH'])) {
    echo "┌─── ❌ GRADE MISMATCHES (" . count($grouped['GRADE_MISMATCH']) . ") ───────────────────────────\n";
    foreach ($grouped['GRADE_MISMATCH'] as $r) {
        printf("│  %-12s  %-28s  %-35s  Excel=%-4s  DB=%-4s\n",
            $r['series'], $r['cand_no'] . ' ' . $r['name'], $r['subject'], $r['excel'], $r['db']);
    }
    echo "└──────────────────────────────────────────────────────────\n\n";
}

// RESULT_NOT_IN_DB
if (!empty($grouped['RESULT_NOT_IN_DB'])) {
    echo "┌─── ⚠  RESULTS MISSING FROM DB (" . count($grouped['RESULT_NOT_IN_DB']) . ") ──────────────────\n";
    foreach ($grouped['RESULT_NOT_IN_DB'] as $r) {
        printf("│  %-12s  %-28s  %-35s  Excel=%s\n",
            $r['series'], $r['cand_no'] . ' ' . $r['name'], $r['subject'], $r['excel']);
    }
    echo "└──────────────────────────────────────────────────────────\n\n";
}

// CANDIDATE_NOT_IN_DB
if (!empty($grouped['CANDIDATE_NOT_IN_DB'])) {
    echo "┌─── ⚠  CANDIDATES NOT IN DB (" . count($grouped['CANDIDATE_NOT_IN_DB']) . ") ──────────────────\n";
    $shown = [];
    foreach ($grouped['CANDIDATE_NOT_IN_DB'] as $r) {
        $key = $r['series'] . '|' . $r['cand_no'];
        if (!isset($shown[$key])) {
            printf("│  %-12s  %-28s  (file: %s)\n", $r['series'], $r['cand_no'] . ' ' . $r['name'], $r['file']);
            $shown[$key] = true;
        }
    }
    echo "└──────────────────────────────────────────────────────────\n\n";
}

// NO_MAPPING
if (!empty($grouped['NO_MAPPING'])) {
    echo "┌─── ℹ  UNMAPPED SUBJECT NAMES (" . count($grouped['NO_MAPPING']) . ") ─────────────────────\n";
    $uniqSubjects = array_unique(array_column($grouped['NO_MAPPING'], 'subject'));
    foreach ($uniqSubjects as $s) {
        echo "│  $s\n";
    }
    echo "└──────────────────────────────────────────────────────────\n\n";
}

// Summary
echo "╔══════════════════════════════╗\n";
echo "║          SUMMARY             ║\n";
echo "╠══════════════════════════════╣\n";
printf("║  ✅ Matched       : %6d   ║\n", $totalMatch);
printf("║  ❌ Mismatches    : %6d   ║\n", $totalMismatch);
printf("║  ⚠  Missing in DB : %6d   ║\n", $totalMissing);
printf("║  ℹ  No mapping    : %6d   ║\n", $totalSkipped);
printf("║  ━━ Total checked : %6d   ║\n", $totalChecked + $totalSkipped);
echo "╚══════════════════════════════╝\n";
