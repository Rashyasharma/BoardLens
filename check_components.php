<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$subjectId = '01ksffsqqn44k67s3fxpwq8edc';
$series = App\Models\ExamSeries::find('019e5ed2-bdb6-7240-8f2f-a4af758f4fbf');
$cs = App\Models\ComponentSet::findForSubjectYear($subjectId, $series->year);

echo "Subject: " . $subjectId . "\n";
echo "Series Year: " . $series->year . "\n";
if ($cs) {
    echo "ComponentSet ID: " . $cs->id . " (is_default: " . ($cs->is_default ? 'yes' : 'no') . ")\n";
    echo "Components count: " . $cs->components()->count() . "\n";
} else {
    echo "No component set found!\n";
}
