<?php
require_once 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

error_reporting(0);
$db = new SQLite3('database/database.sqlite');

$broadsheetDir = 'D:/Rashya Sharma/CIE/Other Docs/CIE ALL Broadsheets/Provisional Component Marks';

// Load DB series
$dbSeries = [];
$r = $db->query("SELECT id, series_code FROM exam_series");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) { 
    $dbSeries[$row['series_code']] = $row['id']; 
}

// Load DB subjects
$dbSubjects = [];
$r = $db->query("SELECT s.id, s.subject_code, q.qualification_type FROM subjects s JOIN qualifications q ON s.qualification_id=q.id");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) { 
    $dbSubjects[$row['qualification_type']][$row['subject_code']] = $row; 
}

// Load DB components
$dbComponents = [];
$r = $db->query("SELECT id, component_code, subject_id FROM components");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) { 
    $dbComponents[$row['subject_id']][$row['component_code']] = $row['id']; 
}

// Load DB results with enrollment details to link correctly
$dbResults = [];
$r = $db->query("
    SELECT sr.id as result_id, sr.enrollment_id, s.id as subject_id, s.subject_code, es.series_code, cand.candidate_number, q.qualification_type 
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

// Truncate existing component marks before importing to ensure clean data
echo "Cleaning existing component marks..." . PHP_EOL;
$db->exec("DELETE FROM component_marks");

echo "Starting import of component marks..." . PHP_EOL;

$db->exec('BEGIN TRANSACTION');

$insertStmt = $db->prepare("
    INSERT INTO component_marks (
        id, subject_result_id, enrollment_id, component_id, 
        obtained_marks, total_marks, percentage, grade, remarks, 
        uploaded_by, uploaded_at, created_at, updated_at
    ) VALUES (
        :id, :subject_result_id, :enrollment_id, :component_id, 
        :obtained_marks, :total_marks, :percentage, :grade, NULL, 
        'system_import', datetime('now'), datetime('now'), datetime('now')
    )
");

$totalImported = 0;

foreach ($fileMap as $fInfo) {
    if (!isset($dbSeries[$fInfo['series_code']])) continue;
    
    echo "Processing broadsheet: {$fInfo['filename']} ({$fInfo['series_code']})" . PHP_EOL;
    
    try {
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
            
            // Extract component columns and details
            $components = [];
            foreach ($row3 as $col => $val) {
                if ($val !== null && preg_match('/^Component\s+(\d+)/i', trim($val), $matches)) {
                    $compNum = $matches[1];
                    
                    // Col = component name start.
                    // Row 4: Col = 'Raw mark', Col+1 = 'Adjusted mark', Col+2 = 'Grade'
                    // Col+1 and Col+2 letters calculation
                    $colIndex = 0;
                    $tempCol = $col;
                    $len = strlen($tempCol);
                    for ($charIdx = 0; $charIdx < $len; $charIdx++) {
                        $colIndex = $colIndex * 26 + (ord($tempCol[$charIdx]) - ord('A') + 1);
                    }
                    
                    // Function to convert index back to Excel Column Letter
                    $getColLetter = function($idx) {
                        $letter = "";
                        while ($idx > 0) {
                            $mod = ($idx - 1) % 26;
                            $letter = chr(65 + $mod) . $letter;
                            $idx = intval(($idx - $mod) / 26);
                        }
                        return $letter;
                    };
                    
                    $rawCol = $col;
                    $adjCol = $getColLetter($colIndex + 1);
                    $gradeCol = $getColLetter($colIndex + 2);
                    
                    // Total marks for component is located in row 4 of $rawCol
                    $compTotalMarks = 100; // default fallback
                    if (isset($row4[$rawCol]) && is_numeric(trim($row4[$rawCol]))) {
                        $compTotalMarks = intval(trim($row4[$rawCol]));
                    }
                    
                    $components[$compNum] = [
                        'raw_col' => $rawCol,
                        'grade_col' => $gradeCol,
                        'total_marks' => $compTotalMarks
                    ];
                }
            }
            
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
                
                $key = $fInfo['series_code'] . '|' . $subjectCode . '|' . $candNo;
                $dbRecord = $dbResults[$key] ?? null;
                if (!$dbRecord) continue; // Candidate result not in DB
                
                $subjId = $dbRecord['subject_id'];
                
                // Save component marks
                foreach ($components as $compNum => $compInfo) {
                    $rawMark = isset($row[$compInfo['raw_col']]) ? trim($row[$compInfo['raw_col']]) : '';
                    $compGrade = isset($row[$compInfo['grade_col']]) ? trim($row[$compInfo['grade_col']]) : '';
                    
                    // Skip if both raw mark and grade are empty/absent
                    if ($rawMark === '' || $rawMark === '-' || strtoupper($rawMark) === 'X') {
                        continue;
                    }
                    
                    // Find component ID from DB components list
                    $compId = $dbComponents[$subjId][$compNum] ?? null;
                    if (!$compId) {
                        // If missing, generate dynamically
                        $compId = bin2hex(random_bytes(13));
                        $dbComponents[$subjId][$compNum] = $compId;
                        
                        $stmtComp = $db->prepare("
                            INSERT INTO components (id, subject_id, component_code, component_name, component_type, total_marks, scaling_factor, is_mandatory, created_at, updated_at)
                            VALUES (:id, :subject_id, :code, :name, 'paper', :total_marks, 1, 1, datetime('now'), datetime('now'))
                        ");
                        $stmtComp->bindValue(':id', $compId, SQLITE3_TEXT);
                        $stmtComp->bindValue(':subject_id', $subjId, SQLITE3_TEXT);
                        $stmtComp->bindValue(':code', $compNum, SQLITE3_TEXT);
                        $stmtComp->bindValue(':name', "Component " . $compNum, SQLITE3_TEXT);
                        $stmtComp->bindValue(':total_marks', $compInfo['total_marks'], SQLITE3_INTEGER);
                        $stmtComp->execute();
                    }
                    
                    $obtainedMarks = floatval($rawMark);
                    $totalMarks = $compInfo['total_marks'];
                    $percentage = $totalMarks > 0 ? ($obtainedMarks / $totalMarks) * 100 : 0;
                    
                    $markId = bin2hex(random_bytes(13));
                    
                    $insertStmt->bindValue(':id', $markId, SQLITE3_TEXT);
                    $insertStmt->bindValue(':subject_result_id', $dbRecord['result_id'], SQLITE3_TEXT);
                    $insertStmt->bindValue(':enrollment_id', $dbRecord['enrollment_id'], SQLITE3_TEXT);
                    $insertStmt->bindValue(':component_id', $compId, SQLITE3_TEXT);
                    $insertStmt->bindValue(':obtained_marks', $obtainedMarks, SQLITE3_FLOAT);
                    $insertStmt->bindValue(':total_marks', $totalMarks, SQLITE3_INTEGER);
                    $insertStmt->bindValue(':percentage', $percentage, SQLITE3_FLOAT);
                    $insertStmt->bindValue(':grade', $compGrade === '' ? null : $compGrade, SQLITE3_TEXT);
                    
                    $insertStmt->execute();
                    $insertStmt->reset();
                    
                    $totalImported++;
                }
            }
        }
        
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        
    } catch (Exception $e) {
        echo "Error reading file {$fInfo['filename']}: " . $e->getMessage() . PHP_EOL;
    }
}

$db->exec('COMMIT');

echo "SUCCESS: Imported {$totalImported} component marks records!" . PHP_EOL;

// Final DB stats check
$cnt = $db->querySingle("SELECT COUNT(*) FROM component_marks");
$cntWithGrade = $db->querySingle("SELECT COUNT(*) FROM component_marks WHERE grade IS NOT NULL AND grade != ''");
echo "Total records in component_marks table: {$cnt}" . PHP_EOL;
echo "Records with non-empty grade: {$cntWithGrade}" . PHP_EOL;
