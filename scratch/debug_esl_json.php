<?php
// Check what JSON the controller actually outputs for qualificationData
// Specifically for the ESL subject

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Qualification;

$qualifications = Qualification::all()->map(function ($qual) {
    return [
        'id' => $qual->id,
        'qualification_name' => $qual->qualification_name,
        'qualification_type' => $qual->qualification_type,
        'subjects' => $qual->subjects()->with([
            'componentSets' => function ($query) {
                $query->with(['components' => function ($q) {
                    $q->orderBy('component_code');
                }])
                ->orderByRaw("CASE WHEN is_default = 0 THEN 0 ELSE 1 END ASC")
                ->orderByRaw("COALESCE(end_year, start_year, 0) DESC");
            }
        ])->get()->map(function ($subject) {
            $allComponents = collect();
            foreach ($subject->componentSets as $set) {
                foreach ($set->components as $c) {
                    $c->component_set = [
                        'start_year' => $set->start_year,
                        'end_year' => $set->end_year,
                        'label' => $set->label
                    ];
                    $allComponents->push($c);
                }
            }
            return [
                'id' => $subject->id,
                'subject_name' => $subject->subject_name,
                'subject_code' => $subject->subject_code,
                'components' => $allComponents
            ];
        })
    ];
});

// Find ESL
foreach ($qualifications as $qual) {
    foreach ($qual['subjects'] as $subj) {
        if (stripos($subj['subject_name'], 'Second') !== false) {
            echo "Subject: {$subj['subject_name']} ({$subj['subject_code']})\n";
            echo "Total components: " . count($subj['components']) . "\n\n";
            foreach ($subj['components'] as $c) {
                $cs = $c->component_set ?? null;
                echo "[{$c->component_code}] {$c->component_label} | set: " . json_encode($cs) . "\n";
            }
        }
    }
}
