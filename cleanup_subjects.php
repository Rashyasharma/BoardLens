<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// 1. Find duplicate subjects (same code + same qualification_id)
echo "=== Checking for duplicate subjects ===\n";
$dupes = DB::table('subjects')
    ->select('subject_code', 'qualification_id', DB::raw('COUNT(*) as cnt'))
    ->groupBy('subject_code', 'qualification_id')
    ->having('cnt', '>', 1)
    ->get();

if ($dupes->isEmpty()) {
    echo "No duplicate subjects found.\n";
} else {
    foreach ($dupes as $d) {
        echo "Duplicate: code={$d->subject_code}, qual_id={$d->qualification_id}, count={$d->cnt}\n";
        // Keep the first one, delete the rest
        $subjects = DB::table('subjects')
            ->where('subject_code', $d->subject_code)
            ->where('qualification_id', $d->qualification_id)
            ->orderBy('created_at')
            ->get();
        
        $keep = $subjects->first();
        $remove = $subjects->slice(1);
        
        foreach ($remove as $r) {
            echo "  Removing duplicate: {$r->id} ({$r->subject_name})\n";
            DB::table('component_marks')->whereIn('component_id', 
                DB::table('components')->where('subject_id', $r->id)->pluck('id')
            )->delete();
            DB::table('components')->where('subject_id', $r->id)->delete();
            DB::table('component_sets')->where('subject_id', $r->id)->delete();
            DB::table('subject_results')->where('subject_id', $r->id)->delete();
            DB::table('candidate_enrollments')->where('subject_id', $r->id)->delete();
            DB::table('subjects')->where('id', $r->id)->delete();
        }
        echo "  Kept: {$keep->id} ({$keep->subject_name})\n";
    }
}

// 2. Also check for subjects with same code across different qualifications (just report)
echo "\n=== All subjects ===\n";
$all = DB::table('subjects')
    ->join('qualifications', 'subjects.qualification_id', '=', 'qualifications.id')
    ->select('subjects.id', 'subjects.subject_code', 'subjects.subject_name', 'qualifications.qualification_name', 'qualifications.qualification_type')
    ->orderBy('subjects.subject_code')
    ->get();

foreach ($all as $s) {
    echo "{$s->subject_code}  {$s->subject_name}  [{$s->qualification_type}]\n";
}
echo "\nTotal subjects: " . $all->count() . "\n";

// 3. Clear all components and component sets
echo "\n=== Clearing components ===\n";
$cmDeleted = DB::table('component_marks')->delete();
echo "Deleted {$cmDeleted} component_marks\n";

$compDeleted = DB::table('components')->delete();
echo "Deleted {$compDeleted} components\n";

$setsDeleted = DB::table('component_sets')->delete();
echo "Deleted {$setsDeleted} component_sets\n";

echo "\n✅ Done. Subjects kept, components cleared, duplicates removed.\n";
