<?php

require 'C:/Users/HP11/Desktop/My Projects/CambridgeInsights/vendor/autoload.php';
$app = require_once 'C:/Users/HP11/Desktop/My Projects/CambridgeInsights/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Subject;
use App\Models\Component;
use App\Models\SubjectResult;
use App\Models\ComponentMarks;
use App\Models\CandidateEnrollment;
use App\Models\ExamSeries;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();

try {
    // 1. Get subjects
    $oldSubject = Subject::where('subject_code', '0510')->first();
    $newSubject = Subject::where('subject_code', '0510_OLD')->first();

    if (!$oldSubject || !$newSubject) {
        throw new \Exception("Old (0510) or New (0510_OLD) subject not found.");
    }

    echo "Old Subject ID: {$oldSubject->id}\n";
    echo "New Subject ID: {$newSubject->id}\n\n";

    // Get old components
    $oldComp1 = Component::where('subject_id', $oldSubject->id)->where('component_code', '01ksffsqqn44k67s3fxpwq8edc')->first();
    if (!$oldComp1) {
        // Fallback to searching by code or name
        $oldComp1 = Component::where('subject_id', $oldSubject->id)->where('component_name', 'like', '%Reading%')->first();
    }
    $oldComp2 = Component::where('subject_id', $oldSubject->id)->where('component_name', 'like', '%Listening%')->first();
    $oldComp3 = Component::where('subject_id', $oldSubject->id)->where('component_name', 'like', '%Speaking%')->first();

    // Get new components
    $newComp2 = Component::where('subject_id', $newSubject->id)->where('component_code', '02')->first();
    $newComp4 = Component::where('subject_id', $newSubject->id)->where('component_code', '04')->first();
    $newComp5 = Component::where('subject_id', $newSubject->id)->where('component_code', '05')->first();

    if (!$newComp2 || !$newComp4 || !$newComp5) {
        throw new \Exception("New components (02, 04, 05) not found under 0510_OLD.");
    }

    // 2. Find pre-2024 series IDs
    $pre2024SeriesIds = ExamSeries::where('year', '<', 2024)->pluck('id')->toArray();
    echo "Pre-2024 Exam Series Count: " . count($pre2024SeriesIds) . "\n";

    // 3. Update candidate enrollments for specific subjects
    $enrollmentsUpdated = CandidateEnrollment::where('subject_id', $oldSubject->id)
        ->whereIn('series_id', $pre2024SeriesIds)
        ->update(['subject_id' => $newSubject->id]);
    echo "Updated candidate_enrollments: {$enrollmentsUpdated}\n";

    // 4. Find subject results
    $results = SubjectResult::where('subject_id', $oldSubject->id)
        ->whereIn('series_id', $pre2024SeriesIds)
        ->get();

    echo "Found " . $results->count() . " subject results to remap.\n";

    $resultsUpdated = 0;
    $compMarksUpdated = 0;

    foreach ($results as $res) {
        // Update subject result subject_id
        $res->subject_id = $newSubject->id;
        $res->save();
        $resultsUpdated++;

        // Get component marks
        $compMarks = ComponentMarks::where('subject_result_id', $res->id)->get();
        foreach ($compMarks as $mark) {
            $mapped = false;

            // Map old components to new ones
            if ($oldComp1 && $mark->component_id === $oldComp1->id) {
                $mark->component_id = $newComp2->id;
                $mark->total_marks = 80;
                $mark->percentage = round(($mark->obtained_marks / 80) * 100, 2);
                $mark->save();
                $compMarksUpdated++;
                $mapped = true;
            } elseif ($oldComp2 && $mark->component_id === $oldComp2->id) {
                $mark->component_id = $newComp4->id;
                $mark->total_marks = 40;
                $mark->percentage = round(($mark->obtained_marks / 40) * 100, 2);
                $mark->save();
                $compMarksUpdated++;
                $mapped = true;
            } elseif ($oldComp3 && $mark->component_id === $oldComp3->id) {
                $mark->component_id = $newComp5->id;
                $mark->total_marks = 30;
                $mark->percentage = round(($mark->obtained_marks / 30) * 100, 2);
                $mark->save();
                $compMarksUpdated++;
                $mapped = true;
            }

            if (!$mapped) {
                // Heuristic fallback matching by component code / name if direct ID matches failed
                $compName = $mark->component->component_name ?? '';
                if (stripos($compName, 'Reading') !== false) {
                    $mark->component_id = $newComp2->id;
                    $mark->total_marks = 80;
                    $mark->percentage = round(($mark->obtained_marks / 80) * 100, 2);
                    $mark->save();
                    $compMarksUpdated++;
                } elseif (stripos($compName, 'Listening') !== false) {
                    $mark->component_id = $newComp4->id;
                    $mark->total_marks = 40;
                    $mark->percentage = round(($mark->obtained_marks / 40) * 100, 2);
                    $mark->save();
                    $compMarksUpdated++;
                } elseif (stripos($compName, 'Speaking') !== false) {
                    $mark->component_id = $newComp5->id;
                    $mark->total_marks = 30;
                    $mark->percentage = round(($mark->obtained_marks / 30) * 100, 2);
                    $mark->save();
                    $compMarksUpdated++;
                }
            }
        }

        // Recalculate totals/percentages from new components
        $res->load('componentMarks');
        $res->calculateFromComponents();
    }

    DB::commit();
    echo "Mapping completed successfully!\n";
    echo "Subject Results Remapped: {$resultsUpdated}\n";
    echo "Component Marks Rows Updated: {$compMarksUpdated}\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
