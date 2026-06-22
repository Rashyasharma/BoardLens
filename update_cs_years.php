<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Subject;
use App\Models\ComponentSet;

// 9608 (2020 - 2021)
$sub9608 = Subject::where('subject_code', '9608')->first();
if ($sub9608) {
    $set9608 = ComponentSet::where('subject_id', $sub9608->id)->first();
    if ($set9608) {
        $set9608->start_year = 2020;
        $set9608->end_year = 2021;
        $set9608->is_default = false; // Set to false if you want strict year matching, or true if it's the only one
        $set9608->label = '2020 – 2021';
        $set9608->save();
        echo "Updated 9608 component set to 2020-2021.\n";
    }
}

// 9618 (2022 onwards)
$sub9618 = Subject::where('subject_code', '9618')->first();
if ($sub9618) {
    $set9618 = ComponentSet::where('subject_id', $sub9618->id)->first();
    if ($set9618) {
        $set9618->start_year = 2022;
        $set9618->end_year = null;
        $set9618->is_default = true; 
        $set9618->label = '2022 – Present';
        $set9618->save();
        echo "Updated 9618 component set to 2022 onwards.\n";
    }
}
