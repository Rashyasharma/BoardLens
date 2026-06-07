<?php

require 'C:/Users/HP11/Desktop/My Projects/CambridgeInsights/vendor/autoload.php';
$app = require_once 'C:/Users/HP11/Desktop/My Projects/CambridgeInsights/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Subject;
use App\Models\Component;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();

try {
    $subject = Subject::where('subject_code', '0510_OLD')->first();
    if (!$subject) {
        throw new \Exception("Subject 0510_OLD not found.");
    }

    echo "Subject: {$subject->subject_name} ({$subject->subject_code})\n";

    // 1. Update component 02 to 22
    $comp2 = Component::where('subject_id', $subject->id)->where('component_code', '02')->first();
    if ($comp2) {
        $comp2->update(['component_code' => '22']);
        echo "Updated Component 02 -> Code: 22 ({$comp2->component_name})\n";
    } else {
        echo "Component 02 not found or already updated.\n";
    }

    // 2. Update component 04 to 42
    $comp4 = Component::where('subject_id', $subject->id)->where('component_code', '04')->first();
    if ($comp4) {
        $comp4->update(['component_code' => '42']);
        echo "Updated Component 04 -> Code: 42 ({$comp4->component_name})\n";
    } else {
        echo "Component 04 not found or already updated.\n";
    }

    // 3. Update component 05 to 52
    $comp5 = Component::where('subject_id', $subject->id)->where('component_code', '05')->first();
    if ($comp5) {
        $comp5->update(['component_code' => '52']);
        echo "Updated Component 05 -> Code: 52 ({$comp5->component_name})\n";
    } else {
        echo "Component 05 not found or already updated.\n";
    }

    DB::commit();
    echo "Component codes updated successfully!\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
