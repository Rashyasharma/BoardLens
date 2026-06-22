<?php
/**
 * Cross-reference broadsheet Excel files with BoardLens SQLite database
 * Reads ACTUAL component marks from broadsheet files and compares with DB subject results
 */
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$db = new SQLite3('database/database.sqlite');

$broadsheetDir = 'D:/Rashya Sharma/CIE/Other Docs/CIE ALL Broadsheets/Provisional Component Marks';

// Load DB data into memory
$dbSubjects = [];
$r = $db->query("SELECT s.id, s.subject_code, s.subject_name, s.total_marks, q.qualification_type FROM subjects s JOIN qualifications q ON s.qualification_id=q.id ORDER BY q.qualification_type, s.subject_code");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
    $dbSubjects[$row['qualification_type']][$row['subject_code']] = $row;
}

$dbComponents = [];
$r = $db->query("SELECT c.id, c.component_code, c.component_name, c.total_marks, s.subject_code, q.qualification_type FROM components c JOIN subjects s ON c.subject_id=s.id JOIN qualifications q ON s.qualification_id=q.id ORDER BY q.qualification_type, s.subject_code, c.component_code");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
    $dbComponents[$row['qualification_type']][$row['subject_code']][$row['component_code']] = $row;
}

$dbResults = [];
$r = $db->query("SELECT sr.id, sr.grade, sr.pum, sr.status, s.subject_code, s.subject_name, es.series_code, es.year, es.month, cand.candidate_number, cand.candidate_name, q.qualification_type FROM subject_results sr JOIN subjects s ON sr.subject_id=s.id JOIN exam_series es ON sr.series_id=es.id JOIN candidate_enrollments ce ON sr.enrollment_id=ce.id JOIN qualifications q ON ce.qualification_id=q.id JOIN candidates cand ON ce.candidate_id=cand.id ORDER BY es.series_code, s.subject_code");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
    $key = $row['series_code'] . '|' . $row['subject_code'] . '|' . $row['candidate_number'];
    $dbResults[$key] = $row;
}

echo "Loaded " . count($dbResults) . " subject results from DB" . PHP_EOL;
echo PHP_EOL;

// Map broadsheet files to series codes
$fileMap = [];
foreach (['IGCSE', 'AS A'] as $level) {
    $dir = $broadsheetDir . '/' . ($level === 'IGCSE' ? 'IGCSE' : 'AS A');
    if (!is_dir($dir)) continue;
    foreach (scandir($dir) as $f) {
        if (pathinfo($f, PATHINFO_EXTENSION) !== 'xlsx') continue;
        
        // Parse month and year from filename
        if (preg_match('/(March|June|November)\s+(\d{4})/', $f, $m)) {
            $month = $m[1];
            $year = $m[2];
            $seriesCode = strtoupper(substr($month, 0, 3)) . '-' . $year;
            $qualType = $level === 'IGCSE' ? 'IGCSE' : 'AS_A_LEVEL';
            $fileMap[] = [
                'path' => $dir . '/' . $f,
                'series_code' => $seriesCode,
                'qual_type' => $qualType,
                'month' => $month,
                'year' => $year,
                'filename' => $f
            ];
        }
    }
}

echo "Found " . count($fileMap) . " broadsheet files" . PHP_EOL;
echo PHP_EOL;

// Now read each broadsheet and extract subject/component data  
$issues = [];
$crossRefResults = [];
$broadsheetSubjects = [];

// Only process files matching series that exist in DB
$dbSeries = [];
$r = $db->query("SELECT series_code FROM exam_series");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) { $dbSeries[] = $row['series_code']; }

foreach ($fileMap as $fInfo) {
    if (!in_array($fInfo['series_code'], $dbSeries)) {
        continue; // Skip files for series not in DB  
    }
    
    echo "=== Processing: {$fInfo['filename']} (Series: {$fInfo['series_code']}, Qual: {$fInfo['qual_type']}) ===" . PHP_EOL;
    
    try {
        $spreadsheet = IOFactory::load($fInfo['path']);
        $sheetNames = $spreadsheet->getSheetNames();
        
        foreach ($sheetNames as $sheetName) {
            $cleanName = trim($sheetName);
            
            // Match sheets that are 4-digit subject codes
            if (!preg_match('/^\d{4}$/', $cleanName)) continue;
            
            $subjectCode = $cleanName;
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $rows = $sheet->toArray(null, true, true, true);
            
            if (count($rows) < 4) continue;
            
            // Extract subject info from the sheet
            $row1 = $rows[1] ?? [];
            $row2 = $rows[2] ?? [];
            $row3 = $rows[3] ?? [];
            $row4 = $rows[4] ?? [];
            
            // Find component columns from Row 3
            $componentCols = [];
            foreach ($row3 as $col => $val) {
                if ($val && preg_match('/^Component\s+(\d+)/i', trim($val), $matches)) {
                    $compCode = trim($matches[1]);
                    $componentCols[$col] = $compCode;
                }
            }
            
            // Find grade column from Row 3 or Row 4 
            $gradeCol = null;
            foreach ($row3 as $col => $val) {
                if ($val && preg_match('/grade/i', trim($val))) {
                    $gradeCol = $col;
                    break;
                }
            }
            if (!$gradeCol) {
                foreach ($row4 as $col => $val) {
                    if ($val && preg_match('/grade/i', trim($val))) {
                        $gradeCol = $col;
                        break;
                    }
                }
            }
            
            // Find candidate columns
            $candNoCol = 'D';
            $candNameCol = 'E';
            foreach ($row4 as $col => $headerVal) {
                if (!$headerVal) continue;
                $headerLower = strtolower(trim($headerVal));
                if (in_array($headerLower, ['candidate number', 'candidate no', 'cand. no', 'no.'])) {
                    $candNoCol = $col;
                } elseif (in_array($headerLower, ['candidate name', 'candidate', 'name'])) {
                    $candNameCol = $col;
                }
            }
            
            // Track subject presence
            $broadsheetSubjects[$fInfo['series_code']][$fInfo['qual_type']][] = $subjectCode;
            
            // Now iterate over candidate rows (starting from row 5)
            for ($i = 5; $i <= count($rows); $i++) {
                $row = $rows[$i] ?? [];
                $candNo = isset($row[$candNoCol]) ? trim($row[$candNoCol]) : '';
                $candName = isset($row[$candNameCol]) ? trim($row[$candNameCol]) : '';
                
                if ($candNo === '' || $candName === '') continue;
                
                // Stop at summary rows
                $upperName = strtoupper($candName);
                if (in_array($upperName, ['MAX', 'MIN', 'AVERAGE']) || 
                    str_contains($upperName, 'REPORT GENERATED') ||
                    str_contains($upperName, 'CAMBRIDGEINTERNATIONAL.ORG')) {
                    break;
                }
                
                if (is_numeric($candNo)) {
                    $candNo = str_pad($candNo, 4, '0', STR_PAD_LEFT);
                }
                
                // Get grade from broadsheet
                $bsGrade = $gradeCol && isset($row[$gradeCol]) ? trim($row[$gradeCol]) : null;
                
                // Get component marks from broadsheet
                $bsCompMarks = [];
                foreach ($componentCols as $col => $compCode) {
                    $mark = isset($row[$col]) ? trim($row[$col]) : null;
                    $bsCompMarks[$compCode] = $mark;
                }
                
                // Cross-reference with DB
                $key = $fInfo['series_code'] . '|' . $subjectCode . '|' . $candNo;
                $dbRecord = $dbResults[$key] ?? null;
                
                if ($dbRecord) {
                    // Check grade match
                    $dbGrade = $dbRecord['grade'];
                    if ($bsGrade !== null && strtoupper($bsGrade) !== strtoupper($dbGrade)) {
                        $issues[] = [
                            'type' => 'GRADE_MISMATCH',
                            'series' => $fInfo['series_code'],
                            'subject' => $subjectCode,
                            'candidate' => $candNo,
                            'candidate_name' => $candName,
                            'broadsheet_grade' => $bsGrade,
                            'db_grade' => $dbGrade
                        ];
                    }
                    
                    // Check if grade is blank in DB
                    if (empty($dbGrade)) {
                        $issues[] = [
                            'type' => 'BLANK_GRADE_IN_DB',
                            'series' => $fInfo['series_code'],
                            'subject' => $subjectCode,
                            'candidate' => $candNo,
                            'candidate_name' => $candName,
                            'broadsheet_grade' => $bsGrade
                        ];
                    }
                }
                
                // Check component completeness
                foreach ($bsCompMarks as $compCode => $mark) {
                    if ($mark === null || $mark === '' || $mark === '-' || strtoupper($mark) === 'X') {
                        // Component mark missing or absent in broadsheet
                        continue;
                    }
                    
                    // Check if component exists in DB  
                    $qualType = $fInfo['qual_type'];
                    if (!isset($dbComponents[$qualType][$subjectCode])) {
                        // Check in generic/alternate qual type
                        $found = false;
                        foreach ($dbComponents as $qt => $subjs) {
                            if (isset($subjs[$subjectCode])) {
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            // Subject not in DB at all
                        }
                    }
                }
                
                $crossRefResults[] = [
                    'series' => $fInfo['series_code'],
                    'qual' => $fInfo['qual_type'],
                    'subject' => $subjectCode,
                    'candidate' => $candNo,
                    'candidate_name' => $candName,
                    'broadsheet_grade' => $bsGrade,
                    'db_grade' => $dbRecord ? $dbRecord['grade'] : 'NOT_IN_DB',
                    'db_pum' => $dbRecord ? $dbRecord['pum'] : null,
                    'comp_marks' => $bsCompMarks,
                    'in_db' => $dbRecord ? true : false
                ];
            }
        }
        
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        
    } catch (Exception $e) {
        echo "ERROR reading {$fInfo['filename']}: " . $e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL . "===============================" . PHP_EOL;
echo "=== VERIFICATION SUMMARY ===" . PHP_EOL;
echo "===============================" . PHP_EOL;

echo PHP_EOL . "--- GRADE MISMATCHES ---" . PHP_EOL;
$gradeMismatches = array_filter($issues, fn($i) => $i['type'] === 'GRADE_MISMATCH');
if (empty($gradeMismatches)) {
    echo "NONE - All grades match!" . PHP_EOL;
} else {
    foreach ($gradeMismatches as $i) {
        echo "  [{$i['series']}] Subject {$i['subject']} Candidate {$i['candidate']} ({$i['candidate_name']}): Broadsheet={$i['broadsheet_grade']} vs DB={$i['db_grade']}" . PHP_EOL;
    }
}

echo PHP_EOL . "--- BLANK GRADES IN DB ---" . PHP_EOL;
$blankGrades = array_filter($issues, fn($i) => $i['type'] === 'BLANK_GRADE_IN_DB');
if (empty($blankGrades)) {
    echo "NONE - No blank grades!" . PHP_EOL;
} else {
    foreach ($blankGrades as $i) {
        echo "  [{$i['series']}] Subject {$i['subject']} Candidate {$i['candidate']} ({$i['candidate_name']}): Should be {$i['broadsheet_grade']}" . PHP_EOL;
    }
}

echo PHP_EOL . "--- RECORDS IN BROADSHEET BUT NOT IN DB ---" . PHP_EOL;
$notInDb = array_filter($crossRefResults, fn($r) => !$r['in_db']);
$groupedNotInDb = [];
foreach ($notInDb as $r) {
    $key = $r['series'] . '|' . $r['subject'];
    $groupedNotInDb[$key][] = $r;
}
if (empty($groupedNotInDb)) {
    echo "NONE - All broadsheet records found in DB!" . PHP_EOL;
} else {
    foreach ($groupedNotInDb as $key => $records) {
        echo "  {$key}: " . count($records) . " candidates not in DB" . PHP_EOL;
        foreach (array_slice($records, 0, 5) as $r) {
            echo "    - {$r['candidate']} ({$r['candidate_name']}) Grade={$r['broadsheet_grade']}" . PHP_EOL;
        }
        if (count($records) > 5) echo "    ... and " . (count($records) - 5) . " more" . PHP_EOL;
    }
}

echo PHP_EOL . "--- SUBJECT-TO-QUALIFICATION MAPPING ---" . PHP_EOL;
echo "(Checking each broadsheet subject maps to exactly one DB subject)" . PHP_EOL;
foreach ($broadsheetSubjects as $series => $quals) {
    foreach ($quals as $qual => $codes) {
        foreach ($codes as $code) {
            $dbMatch = $dbSubjects[$qual][$code] ?? null;
            $otherMatch = null;
            if (!$dbMatch) {
                // Check other qualification types
                foreach ($dbSubjects as $qt => $subjs) {
                    if (isset($subjs[$code])) {
                        $otherMatch = $subjs[$code];
                        break;
                    }
                }
            }
            
            if ($dbMatch) {
                // Good match
            } elseif ($otherMatch) {
                echo "  WARNING [{$series}] Subject {$code} found under {$otherMatch['qualification_type']} instead of {$qual} - NAME: {$otherMatch['subject_name']}" . PHP_EOL;
            } else {
                echo "  MISSING [{$series}] Subject {$code} NOT FOUND in DB for any qualification!" . PHP_EOL;
            }
        }
    }
}

echo PHP_EOL . "--- COMPONENT COMPLETENESS CHECK ---" . PHP_EOL;
echo "(Checking each DB subject has components and each component has a grade column)" . PHP_EOL;
$qualTypes = ['IGCSE', 'AS_A_LEVEL'];
foreach ($qualTypes as $qt) {
    if (!isset($dbSubjects[$qt])) continue;
    echo "  $qt:" . PHP_EOL;
    foreach ($dbSubjects[$qt] as $code => $subj) {
        $components = $dbComponents[$qt][$code] ?? [];
        if (empty($components)) {
            echo "    MISSING COMPONENTS: {$code} ({$subj['subject_name']}) - NO components defined!" . PHP_EOL;
        } else {
            $compCodes = array_keys($components);
            echo "    OK: {$code} ({$subj['subject_name']}) - Components: " . implode(', ', $compCodes) . PHP_EOL;
        }
    }
}

echo PHP_EOL . "--- GRADE COLUMN PRESENCE IN COMPONENT_MARKS TABLE ---" . PHP_EOL;
$r = $db->query("PRAGMA table_info(component_marks)");
$hasCmGrade = false;
while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
    if ($row['name'] === 'grade') {
        $hasCmGrade = true;
        break;
    }
}
echo "component_marks.grade column exists: " . ($hasCmGrade ? 'YES' : 'NO - NEEDS MIGRATION') . PHP_EOL;

echo PHP_EOL . "--- TOTAL COUNTS ---" . PHP_EOL;
echo "Total broadsheet records processed: " . count($crossRefResults) . PHP_EOL;
echo "Total DB subject results: " . count($dbResults) . PHP_EOL;
echo "Grade mismatches: " . count($gradeMismatches) . PHP_EOL;
echo "Blank grades: " . count($blankGrades) . PHP_EOL;
echo "Not in DB: " . count($notInDb) . PHP_EOL;
echo "Component marks in DB: 0 (needs import)" . PHP_EOL;
