<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\ComponentMarks;
use App\Models\ComponentSet;
use App\Models\Component;

$marks = ComponentMarks::with([
    'subjectResult.series', 
    'subjectResult.subject',
    'component'
])->get();

$updated = 0;
$orphans = 0;
$mismatches = 0;

$componentSetsCache = [];

foreach ($marks as $mark) {
    if (!$mark->component || !$mark->subjectResult || !$mark->subjectResult->series) {
        continue;
    }

    $year = $mark->subjectResult->series->year;
    $subjectId = $mark->subjectResult->subject_id;
    $code = $mark->component->component_code;

    // Find component set for subject and year
    $cacheKey = $subjectId . '_' . $year;
    if (!isset($componentSetsCache[$cacheKey])) {
        $set = ComponentSet::with('components')
            ->where('subject_id', $subjectId)
            ->where('start_year', '<=', $year)
            ->where(function($q) use ($year) {
                $q->where('end_year', '>=', $year)
                  ->orWhereNull('end_year');
            })
            ->first();
        $componentSetsCache[$cacheKey] = $set;
    }

    $set = $componentSetsCache[$cacheKey];
    
    if ($set) {
        // Find matching component in the set
        $correctComponent = $set->components->firstWhere('component_code', $code);
        
        if ($correctComponent) {
            $isDirty = false;
            
            if ($mark->component_id !== $correctComponent->id) {
                $mark->component_id = $correctComponent->id;
                $isDirty = true;
            }
            
            if ($mark->total_marks != $correctComponent->total_marks) {
                $mark->total_marks = $correctComponent->total_marks;
                
                // Recalculate percentage
                if ($mark->total_marks > 0) {
                    $mark->percentage = min(100, ($mark->obtained_marks / $mark->total_marks) * 100);
                }
                
                $isDirty = true;
            }
            
            if ($isDirty) {
                $mark->save();
                $updated++;
            }
        } else {
            $mismatches++;
        }
    } else {
        // No component set defined for this subject/year.
        // We'll leave it as is.
    }
}

echo "Updated {$updated} component marks to their correct year-specific components." . PHP_EOL;
echo "Found {$mismatches} marks that could not be mapped to any component in the active ComponentSet." . PHP_EOL;

// Delete orphaned components (dynamically created, not attached to any set)
$deletedOrphans = DB::table('components')
    ->where(function($q) {
        $q->whereNull('component_set_id')
          ->orWhere('component_set_id', '');
    })
    ->whereNotIn('id', function($q) {
        $q->select('component_id')->from('component_marks');
    })
    ->delete();

echo "Deleted {$deletedOrphans} unused orphaned components." . PHP_EOL;

// Final validation
$invalidMarks = DB::select("
    SELECT cm.id, cm.obtained_marks, cm.total_marks, c.component_code, s.subject_code
    FROM component_marks cm
    JOIN components c ON cm.component_id = c.id
    JOIN subject_results sr ON cm.subject_result_id = sr.id
    JOIN subjects s ON sr.subject_id = s.id
    WHERE cm.obtained_marks > cm.total_marks
");

echo "Final Validation: Found " . count($invalidMarks) . " marks where obtained > total." . PHP_EOL;
if (count($invalidMarks) > 0) {
    foreach ($invalidMarks as $inv) {
        echo "  [Subject: {$inv->subject_code}] [Comp: {$inv->component_code}] Mark: {$inv->obtained_marks} / {$inv->total_marks}" . PHP_EOL;
    }
}
