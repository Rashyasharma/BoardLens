<?php
/**
 * DEFINITIVE grade extraction and DB fix script
 * Reads the correct "Syllabus grade" column from each broadsheet sheet
 * and updates the DB where grades don't match
 * 
 * STRUCTURE DISCOVERY:
 * - Row 3: Component headers (Component XX, Component YY, ... Final Marks)
 * - Row 4: Column details - for each component: Raw mark, Adjusted mark, Grade
 *          After components: per-component weighted marks, Syllabus total mark, Syllabus grade
 * - The "Syllabus grade" is ALWAYS the LAST meaningful column in row 4
 * - Candidate number is col D, name is col E
 */
require_once 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

error_reporting(0);

$db = new SQLite3('database/database.sqlite');

$broadsheetDir = 'D:/Rashya Sharma/CIE/Other Docs/CIE ALL Broadsheets/Provisional Component Marks';

// Load DB series
$dbSeries = [];
$r = $db->query("SELECT id, series_code FROM exam_series");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) { $dbSeries[$row['series_code']] = $row['id']; }

// Load DB subjects  
$dbSubjects = [];
$r = $db->query("SELECT s.id, s.subject_code, s.subject_name, q.qualification_type FROM subjects s JOIN qualifications q ON s.qualification_id=q.id");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) { $dbSubjects[$row['qualification_type']][$row['subject_code']] = $row; }

// Load DB results with enrollment details
$dbResults = [];
$r = $db->query("
    SELECT sr.id, sr.grade, sr.pum, s.subject_code, es.series_code, cand.candidate_number, cand.candidate_name, q.qualification_type 
    FROM subject_results sr 
    JOIN subjects s ON sr.subject_id=s.id 
    JOIN exam_series es ON sr.series_id=es.id 
    JOIN candidate_enrollments ce ON sr.enrollment_id=ce.id 
    JOIN qualifications q ON ce.qualification_id=q.id 
    JOIN candidates cand ON ce.candidate_id=cand.id
");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
    $key = $row['series_code'] . '|' . $row['subject_code'] . '|' . $row['candidate_number'];
    $dbResults[$key] = $row;
}

echo "Loaded " . count($dbResults) . " DB results" . PHP_EOL;

// Map broadsheet files
$fileMap = [];
foreach (['IGCSE', 'AS A'] as $level) {
    $dir = $broadsheetDir . '/' . ($level === 'IGCSE' ? 'IGCSE' : 'AS A');
    if (!is_dir($dir)) continue;
    foreach (scandir($dir) as $f) {
        if (pathinfo($f, PATHINFO_EXTENSION) !== 'xlsx') continue;
        if (preg_match('/(March|June|November)\s+(\d{4})/', $f, $m)) {
            $month = $m[1]; $year = $m[2];
            $seriesCode = strtoupper(substr($month, 0, 3)) . '-' . $year;
            $qualType = $level === 'IGCSE' ? 'IGCSE' : 'AS_A_LEVEL';
            $fileMap[] = ['path' => $dir . '/' . $f, 'series_code' => $seriesCode, 'qual_type' => $qualType, 'filename' => $f];
        }
    }
}

$fixes = [];
$componentData = []; // subject -> component info from broadsheet
$allGrades = [];
$allComponentMarks = [];

foreach ($fileMap as $fInfo) {
    if (!isset($dbSeries[$fInfo['series_code']])) continue;
    
    echo "Processing: {$fInfo['filename']} ({$fInfo['series_code']})" . PHP_EOL;
    
    $spreadsheet = IOFactory::load($fInfo['path']);
    
    foreach ($spreadsheet->getSheetNames() as $sheetName) {
        $cleanName = trim($sheetName);
        if (!preg_match('/^\d{4}$/', $cleanName)) continue;
        
        $subjectCode = $cleanName;
        $sheet = $spreadsheet->getSheetByName($sheetName);
        $rows = $sheet->toArray(null, true, true, true);
        if (count($rows) < 5) continue;
        
        $row3 = $rows[3] ?? [];
        $row4 = $rows[4] ?? [];
        
        // Find the "Syllabus grade" column - it's the column in row 4 labeled "Syllabus grade"
        $syllabusGradeCol = null;
        $syllabusTotalMarkCol = null;
        foreach ($row4 as $col => $val) {
            if ($val !== null && trim($val) === 'Syllabus grade') {
                $syllabusGradeCol = $col;
            }
            if ($val !== null && trim($val) === 'Syllabus total mark') {
                $syllabusTotalMarkCol = $col;
            }
        }
        
        if (!$syllabusGradeCol) {
            echo "  WARNING: No 'Syllabus grade' column found for subject {$subjectCode}" . PHP_EOL;
            continue;
        }
        
        // Extract component info from Row 3 & Row 4
        $components = [];
        foreach ($row3 as $col => $val) {
            if ($val !== null && preg_match('/^Component\s+(\d+)/i', trim($val), $matches)) {
                $compNum = $matches[1];
                // Find the grade column for this component (H column = col after Adjusted mark)
                // Component starts at col, Raw mark = col, Adjusted mark = col+1, Grade = col+2
                $gradeColLetter = chr(ord($col) + 2);
                if (ord($col) + 2 > ord('Z')) {
                    // Handle double-letter columns (rare)
                    $gradeColLetter = 'A' . chr(ord($col) + 2 - 26 + ord('A') - 1);
                }
                $components[$compNum] = [
                    'raw_col' => $col,
                    'grade_col' => $gradeColLetter,
                    'number' => $compNum
                ];
            }
        }
        
        // Store component definitions
        $componentData[$fInfo['qual_type']][$subjectCode] = array_keys($components);
        
        // Process candidate rows
        for ($i = 5; $i <= count($rows); $i++) {
            $row = $rows[$i] ?? [];
            $candNo = isset($row['D']) ? trim($row['D']) : '';
            $candName = isset($row['E']) ? trim($row['E']) : '';
            
            if ($candNo === '' || $candName === '') continue;
            $upperName = strtoupper($candName);
            if (in_array($upperName, ['MAX', 'MIN', 'AVERAGE']) || str_contains($upperName, 'REPORT GENERATED') || str_contains($upperName, 'CAMBRIDGEINTERNATIONAL')) break;
            
            if (is_numeric($candNo)) {
                $candNo = str_pad($candNo, 4, '0', STR_PAD_LEFT);
            }
            
            // Get the ACTUAL syllabus grade
            $bsGrade = isset($row[$syllabusGradeCol]) ? trim($row[$syllabusGradeCol]) : '';
            $bsPUM = isset($row[$syllabusTotalMarkCol]) ? trim($row[$syllabusTotalMarkCol]) : '';
            
            // Get component marks
            $compMarks = [];
            foreach ($components as $compNum => $compInfo) {
                $rawMark = isset($row[$compInfo['raw_col']]) ? trim($row[$compInfo['raw_col']]) : null;
                $compGrade = isset($row[$compInfo['grade_col']]) ? trim($row[$compInfo['grade_col']]) : null;
                $compMarks[$compNum] = ['raw' => $rawMark, 'grade' => $compGrade];
            }
            
            $key = $fInfo['series_code'] . '|' . $subjectCode . '|' . $candNo;
            $dbRecord = $dbResults[$key] ?? null;
            
            $allGrades[] = [
                'series' => $fInfo['series_code'],
                'qual' => $fInfo['qual_type'],
                'subject' => $subjectCode,
                'candidate' => $candNo,
                'name' => $candName,
                'bs_grade' => $bsGrade,
                'bs_pum' => $bsPUM,
                'db_grade' => $dbRecord ? $dbRecord['grade'] : 'NOT_IN_DB',
                'db_pum' => $dbRecord ? $dbRecord['pum'] : null,
                'db_id' => $dbRecord ? $dbRecord['id'] : null,
                'match' => $dbRecord && strtoupper($bsGrade) === strtoupper($dbRecord['grade'])
            ];
            
            $allComponentMarks[] = [
                'series' => $fInfo['series_code'],
                'qual' => $fInfo['qual_type'],
                'subject' => $subjectCode,
                'candidate' => $candNo,
                'comp_marks' => $compMarks
            ];
            
            // Check for fix needed
            if ($dbRecord && $bsGrade !== '' && strtoupper($bsGrade) !== strtoupper($dbRecord['grade'])) {
                $fixes[] = [
                    'db_id' => $dbRecord['id'],
                    'series' => $fInfo['series_code'],
                    'subject' => $subjectCode,
                    'candidate' => $candNo,
                    'name' => $candName,
                    'old_grade' => $dbRecord['grade'],
                    'new_grade' => $bsGrade,
                    'bs_pum' => $bsPUM,
                    'db_pum' => $dbRecord['pum']
                ];
            }
        }
    }
    
    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);
}

echo PHP_EOL . "========================================" . PHP_EOL;
echo "=== DEFINITIVE VERIFICATION RESULTS ===" . PHP_EOL;
echo "========================================" . PHP_EOL;

$matched = count(array_filter($allGrades, fn($g) => $g['match']));
$mismatched = count(array_filter($allGrades, fn($g) => !$g['match'] && $g['db_grade'] !== 'NOT_IN_DB' && $g['bs_grade'] !== ''));
$bsBlank = count(array_filter($allGrades, fn($g) => $g['bs_grade'] === '' && $g['db_grade'] !== 'NOT_IN_DB'));
$notInDb = count(array_filter($allGrades, fn($g) => $g['db_grade'] === 'NOT_IN_DB'));

echo PHP_EOL . "Total broadsheet records: " . count($allGrades) . PHP_EOL;
echo "Matched (including case-insensitive): {$matched}" . PHP_EOL;
echo "Mismatched (needs fix): {$mismatched}" . PHP_EOL;
echo "Broadsheet grade blank (component-only row): {$bsBlank}" . PHP_EOL;
echo "Not in DB: {$notInDb}" . PHP_EOL;
echo "Fixes to apply: " . count($fixes) . PHP_EOL;

// Show all fixes grouped by series
echo PHP_EOL . "--- ALL GRADE FIXES ---" . PHP_EOL;
$fixesBySeries = [];
foreach ($fixes as $f) {
    $fixesBySeries[$f['series']][] = $f;
}
ksort($fixesBySeries);
foreach ($fixesBySeries as $series => $seriesFixes) {
    echo PHP_EOL . "  === {$series} (" . count($seriesFixes) . " fixes) ===" . PHP_EOL;
    foreach ($seriesFixes as $f) {
        echo "    Subject {$f['subject']} Cand {$f['candidate']} ({$f['name']}): '{$f['old_grade']}' -> '{$f['new_grade']}' (PUM: DB={$f['db_pum']} BS={$f['bs_pum']})" . PHP_EOL;
    }
}

// Show component definitions found
echo PHP_EOL . "--- COMPONENT DEFINITIONS FROM BROADSHEETS ---" . PHP_EOL;
foreach ($componentData as $qual => $subjects) {
    echo "  {$qual}:" . PHP_EOL;
    ksort($subjects);
    foreach ($subjects as $code => $comps) {
        echo "    {$code}: Components " . implode(', ', $comps) . PHP_EOL;
    }
}

// Apply fixes
echo PHP_EOL . "--- APPLYING FIXES ---" . PHP_EOL;
$db->exec('BEGIN TRANSACTION');
$applied = 0;
$stmt = $db->prepare("UPDATE subject_results SET grade = :grade WHERE id = :id");

foreach ($fixes as $f) {
    $stmt->bindValue(':grade', $f['new_grade'], SQLITE3_TEXT);
    $stmt->bindValue(':id', $f['db_id'], SQLITE3_TEXT);
    $stmt->execute();
    $stmt->reset();
    $applied++;
}
$db->exec('COMMIT');
echo "Applied {$applied} grade fixes!" . PHP_EOL;

// Verify fixes
echo PHP_EOL . "--- POST-FIX VERIFICATION ---" . PHP_EOL;
$r = $db->query("SELECT COUNT(*) as cnt FROM subject_results WHERE grade IS NULL OR grade = ''");
$row = $r->fetchArray(SQLITE3_ASSOC);
echo "Blank grades after fix: {$row['cnt']}" . PHP_EOL;

$r = $db->query("SELECT COUNT(*) as cnt FROM subject_results");
$row = $r->fetchArray(SQLITE3_ASSOC);
echo "Total subject results: {$row['cnt']}" . PHP_EOL;
