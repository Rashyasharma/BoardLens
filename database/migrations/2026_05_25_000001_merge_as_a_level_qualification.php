<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Merge AS_LEVEL and A_LEVEL into a single GCE AS_A_LEVEL qualification.
     *
     * Order matters for SQLite:
     *   1. Remove CHECK constraints FIRST (recreate tables as plain strings)
     *   2. Resolve subject code conflicts
     *   3. Re-point foreign keys from loser → winner
     *   4. Rename winner to AS_A_LEVEL / GCE AS and A Level
     *   5. Delete loser row
     */
    public function up(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        // ══════════════════════════════════════════════════════════════════
        // STEP 1: Recreate qualifications WITHOUT the old enum CHECK constraint
        // ══════════════════════════════════════════════════════════════════
        DB::statement('
            CREATE TABLE qualifications_new (
                id TEXT NOT NULL PRIMARY KEY,
                qualification_type TEXT NOT NULL UNIQUE,
                qualification_name TEXT NOT NULL,
                description TEXT,
                is_active INTEGER NOT NULL DEFAULT 1,
                created_at TEXT,
                updated_at TEXT
            )
        ');
        DB::statement('INSERT INTO qualifications_new SELECT * FROM qualifications');
        DB::statement('DROP TABLE qualifications');
        DB::statement('ALTER TABLE qualifications_new RENAME TO qualifications');

        // ══════════════════════════════════════════════════════════════════
        // STEP 2: Recreate grade_thresholds WITHOUT old enum CHECK constraint
        // ══════════════════════════════════════════════════════════════════
        $hasGT = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='grade_thresholds'");
        if (!empty($hasGT)) {
            DB::statement('
                CREATE TABLE grade_thresholds_new (
                    id TEXT NOT NULL PRIMARY KEY,
                    series_id TEXT NOT NULL,
                    subject_id TEXT NOT NULL,
                    grade TEXT NOT NULL,
                    qualification_type TEXT NOT NULL,
                    minimum_pum NUMERIC NOT NULL,
                    maximum_pum NUMERIC,
                    created_at TEXT,
                    updated_at TEXT,
                    UNIQUE(series_id, subject_id, grade)
                )
            ');
            DB::statement('INSERT INTO grade_thresholds_new SELECT * FROM grade_thresholds');
            DB::statement('DROP TABLE grade_thresholds');
            DB::statement('ALTER TABLE grade_thresholds_new RENAME TO grade_thresholds');
        }

        // ══════════════════════════════════════════════════════════════════
        // STEP 3: Identify which qualifications exist
        // ══════════════════════════════════════════════════════════════════
        $asLevel = DB::table('qualifications')
            ->where('qualification_type', 'AS_LEVEL')->first();

        $aLevel = DB::table('qualifications')
            ->where('qualification_type', 'A_LEVEL')->first();

        $winnerId  = null;
        $loserId   = null;
        $loserType = null;

        if ($asLevel && $aLevel) {
            $winnerId  = $asLevel->id;
            $loserId   = $aLevel->id;
            $loserType = 'A'; // A Level is the loser; append -A suffix if code clashes
        } elseif ($asLevel) {
            $winnerId = $asLevel->id;
        } elseif ($aLevel) {
            $winnerId = $aLevel->id;
        }

        // ══════════════════════════════════════════════════════════════════
        // STEP 4: Fix duplicate subject codes before merge
        // ══════════════════════════════════════════════════════════════════
        if ($loserId && $winnerId) {
            $winnerCodes = DB::table('subjects')
                ->where('qualification_id', $winnerId)
                ->pluck('subject_code')
                ->toArray();

            $loserSubjects = DB::table('subjects')
                ->where('qualification_id', $loserId)
                ->get();

            foreach ($loserSubjects as $subj) {
                if (in_array($subj->subject_code, $winnerCodes)) {
                    $base    = $subj->subject_code . '-' . $loserType;
                    $attempt = $base;
                    $i = 2;
                    while (in_array($attempt, $winnerCodes)) {
                        $attempt = $base . $i;
                        $i++;
                    }
                    DB::table('subjects')
                        ->where('id', $subj->id)
                        ->update(['subject_code' => $attempt]);
                    $winnerCodes[] = $attempt;
                } else {
                    $winnerCodes[] = $subj->subject_code;
                }
            }

            // ── Re-point foreign keys ──────────────────────────────────────
            DB::table('subjects')
                ->where('qualification_id', $loserId)
                ->update(['qualification_id' => $winnerId]);

            DB::table('exam_series')
                ->where('qualification_id', $loserId)
                ->update(['qualification_id' => $winnerId]);

            DB::table('candidate_enrollments')
                ->where('qualification_id', $loserId)
                ->update(['qualification_id' => $winnerId]);

            // ── Delete loser row ───────────────────────────────────────────
            DB::table('qualifications')->where('id', $loserId)->delete();
        }

        // ══════════════════════════════════════════════════════════════════
        // STEP 5: Rename winner qualification to AS_A_LEVEL
        // ══════════════════════════════════════════════════════════════════
        if ($winnerId) {
            DB::table('qualifications')
                ->where('id', $winnerId)
                ->update([
                    'qualification_type' => 'AS_A_LEVEL',
                    'qualification_name' => 'GCE AS and A Level',
                    'updated_at'         => now()->toDateTimeString(),
                ]);
        }

        // ══════════════════════════════════════════════════════════════════
        // STEP 6: Update grade_thresholds qualification_type
        // ══════════════════════════════════════════════════════════════════
        DB::table('grade_thresholds')
            ->whereIn('qualification_type', ['AS_LEVEL', 'A_LEVEL'])
            ->update(['qualification_type' => 'AS_A_LEVEL']);

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        DB::table('qualifications')
            ->where('qualification_type', 'AS_A_LEVEL')
            ->update([
                'qualification_type' => 'AS_LEVEL',
                'qualification_name' => 'AS Level',
            ]);

        DB::table('grade_thresholds')
            ->where('qualification_type', 'AS_A_LEVEL')
            ->update(['qualification_type' => 'AS_LEVEL']);
    }
};
