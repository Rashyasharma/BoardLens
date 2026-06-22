<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Updates all "Default" component sets (start_year IS NULL) to year range 2018–2026
     * and gives them a meaningful label.
     */
    public function up(): void
    {
        DB::table('component_sets')
            ->whereNull('start_year')
            ->whereNull('end_year')
            ->where('is_default', true)
            ->update([
                'start_year' => 2018,
                'end_year'   => 2026,
                'label'      => '2018 – 2026',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('component_sets')
            ->where('start_year', 2018)
            ->where('end_year', 2026)
            ->where('is_default', true)
            ->update([
                'start_year' => null,
                'end_year'   => null,
                'label'      => 'Default',
            ]);
    }
};
