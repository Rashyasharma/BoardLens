<?php

require 'C:/Users/HP11/Desktop/My Projects/CambridgeInsights/vendor/autoload.php';
$app = require_once 'C:/Users/HP11/Desktop/My Projects/CambridgeInsights/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Subject;
use App\Models\Component;
use App\Models\ComponentMarks;
use App\Models\SubjectResult;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();

try {
    $subject = Subject::where('subject_code', '0510_OLD')->first();
    if (!$subject) {
        throw new \Exception("Subject 0510_OLD not found.");
    }

    $comp22 = Component::where('subject_id', $subject->id)->where('component_code', '22')->first();
    $comp42 = Component::where('subject_id', $subject->id)->where('component_code', '42')->first();

    if (!$comp22 || !$comp42) {
        throw new \Exception("Components 22 or 42 not found.");
    }

    // Get March 2023 subject results for this subject
    $results = SubjectResult::where('subject_id', $subject->id)
        ->whereHas('series', function($q) {
            $q->where('series_name', 'March 2023');
        })
        ->get();

    echo "Found " . $results->count() . " subject results for March 2023.\n";

    $updatedCount = 0;

    foreach ($results as $res) {
        // Find component marks linked to 42 (Listening)
        $marks = ComponentMarks::where('subject_result_id', $res->id)
            ->where('component_id', $comp42->id)
            ->get();

        foreach ($marks as $mark) {
            // Update to component 22 (Reading and Writing)
            $mark->component_id = $comp22->id;
            $mark->total_marks = 80;
            $mark->percentage = round(($mark->obtained_marks / 80) * 100, 2);
            $mark->save();
            $updatedCount++;
            echo "  Mapped candidate marks for {$res->enrollment->candidate->candidate_name} to Reading and Writing (22): {$mark->obtained_marks} / 80\n";
        }

        // Recalculate totals
        $res->load('componentMarks');
        $res->calculateFromComponents();
    }

    DB::commit();
    echo "Successfully remapped {$updatedCount} Listening marks to Reading & Writing for March 2023!\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
