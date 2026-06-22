<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$results = App\Models\SubjectResult::where(function($q) {
    $q->whereNull('status')->orWhere('status', '!=', 'complete');
})->get();

$count = 0;
foreach ($results as $result) {
    if ($result->componentMarks()->count() > 0) {
        $result->status = 'complete';
        
        // Also fix the uniform mark if it was missed
        $componentMarks = $result->componentMarks()->with('component')->get();
        if ($componentMarks->isNotEmpty()) {
            $totalObtained = $componentMarks->sum('obtained_marks');
            $totalPossible = $componentMarks->sum('total_marks');
            if ($totalPossible > 0) {
                $percentage = ($totalObtained / $totalPossible) * 100;
                
                $totalScaling = 0;
                $weightedSum = 0;
                foreach ($componentMarks as $mark) {
                    $scalingFactor = $mark->component->scaling_factor ?? 1;
                    $totalScaling += $scalingFactor;
                    $weightedSum += ($mark->percentage * $scalingFactor);
                }
                $calculatedUniformMark = $totalScaling > 0 ? ($weightedSum / $totalScaling) : $percentage;
                
                $result->calculated_uniform_mark = round($calculatedUniformMark, 2);
                $result->overall_percentage = round($percentage, 2);
                $result->total_obtained_marks = $totalObtained;
                $result->total_marks = $totalPossible;
            }
        }
        
        $result->save();
        $count++;
    }
}
echo "Fixed $count results.\n";
