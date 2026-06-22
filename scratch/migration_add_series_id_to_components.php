<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Starting database schema update for components (FOREIGN_KEY_CHECKS disabled)...\n";

DB::statement('PRAGMA foreign_keys = OFF');

try {
    DB::transaction(function() {
        // 1. Add series_id to components table if it doesn't exist
        if (!Schema::hasColumn('components', 'series_id')) {
            echo "Adding series_id column to components table...\n";
            Schema::table('components', function ($table) {
                $table->foreignUuid('series_id')->nullable()->constrained('exam_series')->onDelete('cascade');
            });
        }

        // 2. Adjust unique constraints
        echo "Dropping old subject_id_component_code_unique index...\n";
        DB::statement('DROP INDEX IF EXISTS components_subject_id_component_code_unique');

        echo "Creating new subject_id_series_id_component_code_unique composite index...\n";
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS components_subject_series_code_unique ON components(subject_id, series_id, component_code)');
    });
    echo "Schema update completed successfully!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    DB::statement('PRAGMA foreign_keys = ON');
}
