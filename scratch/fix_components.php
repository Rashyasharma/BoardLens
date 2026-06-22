<?php
/**
 * Fix component definitions in the DB:
 * 1. Add missing components for subjects that have none
 * 2. Clean up duplicate component codes (merge P1/1 format)
 * 3. Match broadsheet component numbers
 * 
 * Broadsheet component definitions:
 * IGCSE:
 *   0400: 01, 02            | 0417: 02, 03, 12         | 0450: 12, 22
 *   0452: 12, 22            | 0455: 12, 22             | 0460: 12, 22, 42
 *   0471: 12, 22            | 0472: 03, 12, 22, 42     | 0475: 12, 32, 42
 *   0500: 12, 22            | 0510: 12, 22, 32         | 0520: 03, 12, 22, 42
 *   0549: 01, 02            | 0580: 12, 22, 32, 42     | 0606: 12, 22
 *   0607: 12, 22, 32, 42, 52, 62  | 0610: 12, 32, 62   | 0620: 22, 42, 62
 *   0625: 22, 42, 62        | 0653: 12, 32, 62         | 0654: 22, 42, 62
 *   0680: 12, 22
 * AS_A_LEVEL:
 *   8021: 12, 22            | 9093: 12, 22, 32, 42, 88 | 9479: 01, 02
 *   9489: 12, 22            | 9609: 12, 22, 32, 42     | 9618: 32, 42, 88
 *   9626: 02, 04, 12, 32    | 9695: 12, 22, 32, 42     | 9699: 12, 22, 32, 42
 *   9700: 12, 22, 33, 42, 52, 95  | 9701: 42, 52, 95   | 9702: 12, 22, 33, 42, 52, 95
 *   9706: 12, 22            | 9708: 12, 22, 32, 42, 98 | 9709: 12, 32, 52, 62, 98
 *   9990: 12, 22, 32, 42
 */

error_reporting(0);
$db = new SQLite3('database/database.sqlite');

// Get current subjects and components
$subjects = [];
$r = $db->query("SELECT s.id, s.subject_code, s.subject_name, s.total_marks, q.qualification_type, q.id as qual_id 
    FROM subjects s JOIN qualifications q ON s.qualification_id=q.id ORDER BY q.qualification_type, s.subject_code");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) { $subjects[$row['qualification_type']][$row['subject_code']] = $row; }

$existingComponents = [];
$r = $db->query("SELECT c.id, c.component_code, c.component_name, c.total_marks, c.subject_id, s.subject_code, q.qualification_type 
    FROM components c JOIN subjects s ON c.subject_id=s.id JOIN qualifications q ON s.qualification_id=q.id
    ORDER BY q.qualification_type, s.subject_code, c.component_code");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
    $existingComponents[$row['qualification_type']][$row['subject_code']][] = $row;
}

// Broadsheet-defined component numbers (the ground truth)
$bsComponents = [
    'IGCSE' => [
        '0400' => ['01', '02'],
        '0417' => ['02', '03', '12'],
        '0450' => ['12', '22'],
        '0452' => ['12', '22'],
        '0455' => ['12', '22'],
        '0460' => ['12', '22', '42'],
        '0471' => ['12', '22'],
        '0472' => ['03', '12', '22', '42'],
        '0475' => ['12', '32', '42'],
        '0500' => ['12', '22'],
        '0510' => ['12', '22', '32'],
        '0520' => ['03', '12', '22', '42'],
        '0549' => ['01', '02'],
        '0580' => ['12', '22', '32', '42'],
        '0606' => ['12', '22'],
        '0607' => ['12', '22', '32', '42', '52', '62'],
        '0610' => ['12', '32', '62'],
        '0620' => ['22', '42', '62'],
        '0625' => ['22', '42', '62'],
        '0653' => ['12', '32', '62'],
        '0654' => ['22', '42', '62'],
        '0680' => ['12', '22'],
    ],
    'AS_A_LEVEL' => [
        '8021' => ['12', '22'],
        '9093' => ['12', '22', '32', '42', '88'],
        '9479' => ['01', '02'],
        '9489' => ['12', '22'],
        '9609' => ['12', '22', '32', '42'],
        '9618' => ['32', '42', '88'],
        '9626' => ['02', '04', '12', '32'],
        '9695' => ['12', '22', '32', '42'],
        '9699' => ['12', '22', '32', '42'],
        '9700' => ['12', '22', '33', '42', '52', '95'],
        '9701' => ['42', '52', '95'],
        '9702' => ['12', '22', '33', '42', '52', '95'],
        '9706' => ['12', '22'],
        '9708' => ['12', '22', '32', '42', '98'],
        '9709' => ['12', '32', '52', '62', '98'],
        '9990' => ['12', '22', '32', '42'],
    ]
];

// Component name map
$compNameMap = [
    '01' => 'Component 01',
    '02' => 'Component 02',
    '03' => 'Component 03',
    '04' => 'Component 04',
    '12' => 'Component 12',
    '22' => 'Component 22',
    '32' => 'Component 32',
    '33' => 'Component 33',
    '42' => 'Component 42',
    '52' => 'Component 52',
    '62' => 'Component 62',
    '88' => 'Component 88',
    '95' => 'Component 95',
    '98' => 'Component 98',
];

echo "=== COMPONENT CLEANUP & ADD ===" . PHP_EOL . PHP_EOL;

$db->exec('BEGIN TRANSACTION');

// Step 1: For subjects with MISSING components - add them
$missingSubjects = [
    'IGCSE' => ['0400', '0417', '0450', '0452', '0455', '0460', '0471', '0472', '0475', '0500', '0510', '0520', '0549', '0580', '0606', '0607', '0610', '0620', '0625', '0653', '0654', '0680'],
    'AS_A_LEVEL' => ['8021', '9093', '9479', '9489', '9609', '9618', '9626', '9695', '9699', '9700', '9701', '9702', '9706', '9708', '9709', '9990']
];

foreach ($missingSubjects as $qual => $codes) {
    foreach ($codes as $code) {
        $subject = $subjects[$qual][$code] ?? null;
        if (!$subject) {
            echo "SKIP: Subject {$code} not found in DB for {$qual}" . PHP_EOL;
            continue;
        }
        
        $comps = $bsComponents[$qual][$code] ?? [];
        echo "ADDING components for {$qual}/{$code} ({$subject['subject_name']}): " . implode(', ', $comps) . PHP_EOL;
        
        foreach ($comps as $compNum) {
            // Check if this component code already exists for this subject
            $exists = false;
            if (isset($existingComponents[$qual][$code])) {
                foreach ($existingComponents[$qual][$code] as $ec) {
                    if ($ec['component_code'] === $compNum) {
                        $exists = true;
                        break;
                    }
                }
            }
            if ($exists) {
                continue;
            }
            
            $compName = $compNameMap[$compNum] ?? "Component {$compNum}";
            $id = bin2hex(random_bytes(13)); // Generate unique ID
            $stmt = $db->prepare("INSERT INTO components (id, subject_id, component_code, component_name, component_type, total_marks, scaling_factor, is_mandatory, created_at, updated_at) 
                VALUES (:id, :subject_id, :code, :name, 'paper', 100, 1, 1, datetime('now'), datetime('now'))");
            $stmt->bindValue(':id', $id, SQLITE3_TEXT);
            $stmt->bindValue(':subject_id', $subject['id'], SQLITE3_TEXT);
            $stmt->bindValue(':code', $compNum, SQLITE3_TEXT);
            $stmt->bindValue(':name', $compName, SQLITE3_TEXT);
            $stmt->execute();
        }
    }
}

// Step 2: For subjects with DUPLICATE components (e.g., 1, 2, P1, P2) - 
// delete the old-format ones and update to broadsheet format
echo PHP_EOL . "--- CHECKING FOR DUPLICATE COMPONENTS ---" . PHP_EOL;

foreach ($existingComponents as $qual => $subjectsComps) {
    foreach ($subjectsComps as $code => $comps) {
        $bsExpected = $bsComponents[$qual][$code] ?? null;
        if (!$bsExpected) continue;
        
        $existingCodes = array_map(fn($c) => $c['component_code'], $comps);
        
        // Check if existing codes match broadsheet
        $missingFromDb = array_diff($bsExpected, $existingCodes);
        $extraInDb = array_diff($existingCodes, $bsExpected);
        
        if (!empty($missingFromDb) || !empty($extraInDb)) {
            echo PHP_EOL . "  {$qual}/{$code}:" . PHP_EOL;
            echo "    DB has: " . implode(', ', $existingCodes) . PHP_EOL;
            echo "    Broadsheet expects: " . implode(', ', $bsExpected) . PHP_EOL;
            
            if (!empty($missingFromDb)) {
                echo "    MISSING from DB: " . implode(', ', $missingFromDb) . PHP_EOL;
                
                $subject = $subjects[$qual][$code] ?? null;
                if ($subject) {
                    foreach ($missingFromDb as $compNum) {
                        $compName = $compNameMap[$compNum] ?? "Component {$compNum}";
                        $id = bin2hex(random_bytes(13));
                        $stmt = $db->prepare("INSERT INTO components (id, subject_id, component_code, component_name, component_type, total_marks, scaling_factor, is_mandatory, created_at, updated_at) 
                            VALUES (:id, :subject_id, :code, :name, 'paper', 100, 1, 1, datetime('now'), datetime('now'))");
                        $stmt->bindValue(':id', $id, SQLITE3_TEXT);
                        $stmt->bindValue(':subject_id', $subject['id'], SQLITE3_TEXT);
                        $stmt->bindValue(':code', $compNum, SQLITE3_TEXT);
                        $stmt->bindValue(':name', $compName, SQLITE3_TEXT);
                        $stmt->execute();
                        echo "    ADDED: {$compNum} ({$compName})" . PHP_EOL;
                    }
                }
            }
            
            if (!empty($extraInDb)) {
                echo "    EXTRA in DB (not in broadsheet): " . implode(', ', $extraInDb) . PHP_EOL;
                // Check if there are component_marks linked to these - if so, don't delete
                foreach ($extraInDb as $extraCode) {
                    $extraComp = null;
                    foreach ($comps as $c) {
                        if ($c['component_code'] === $extraCode) { $extraComp = $c; break; }
                    }
                    if ($extraComp) {
                        $r2 = $db->query("SELECT COUNT(*) as cnt FROM component_marks WHERE component_id = '{$extraComp['id']}'");
                        $cnt = $r2->fetchArray(SQLITE3_ASSOC)['cnt'];
                        if ($cnt == 0) {
                            $db->exec("DELETE FROM components WHERE id = '{$extraComp['id']}'");
                            echo "    DELETED: {$extraCode} ({$extraComp['component_name']}) - no marks linked" . PHP_EOL;
                        } else {
                            echo "    KEPT: {$extraCode} ({$extraComp['component_name']}) - has {$cnt} marks linked" . PHP_EOL;
                        }
                    }
                }
            }
        }
    }
}

$db->exec('COMMIT');

// Final verification
echo PHP_EOL . "=== FINAL COMPONENT STATUS ===" . PHP_EOL;
$r = $db->query("SELECT s.subject_code, s.subject_name, q.qualification_type, COUNT(c.id) as comp_count, GROUP_CONCAT(c.component_code, ', ') as comp_codes
    FROM subjects s 
    JOIN qualifications q ON s.qualification_id=q.id
    LEFT JOIN components c ON c.subject_id=s.id
    GROUP BY s.id
    ORDER BY q.qualification_type, s.subject_code");

$noComps = 0;
while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
    $status = $row['comp_count'] > 0 ? 'OK' : 'MISSING';
    if ($row['comp_count'] == 0) $noComps++;
    echo "  [{$row['qualification_type']}] {$row['subject_code']} ({$row['subject_name']}): {$row['comp_count']} components - {$row['comp_codes']} [{$status}]" . PHP_EOL;
}
echo PHP_EOL . "Subjects with no components: {$noComps}" . PHP_EOL;
