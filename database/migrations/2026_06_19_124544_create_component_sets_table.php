<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create the component_sets table
        Schema::create('component_sets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('subject_id');
            $table->integer('start_year')->nullable();   // null = covers all years (default set)
            $table->integer('end_year')->nullable();      // null = "present" / ongoing
            $table->string('label')->nullable();          // e.g. "2018 – 2021" or "Default"
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
        });

        // 2. Add component_set_id column to components table
        Schema::table('components', function (Blueprint $table) {
            $table->uuid('component_set_id')->nullable()->after('subject_id');
        });

        // 3. Migrate existing components into default component sets
        // Only process subjects that actually exist in the subjects table
        $subjectIds = DB::table('components')
            ->whereNull('series_id')
            ->join('subjects', 'components.subject_id', '=', 'subjects.id')
            ->distinct()
            ->pluck('components.subject_id');

        foreach ($subjectIds as $subjectId) {
            $setId = (string) Str::uuid();
            DB::table('component_sets')->insert([
                'id' => $setId,
                'subject_id' => $subjectId,
                'start_year' => null,
                'end_year' => null,
                'label' => 'Default',
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Assign all default components (series_id IS NULL) to this set
            DB::table('components')
                ->where('subject_id', $subjectId)
                ->whereNull('series_id')
                ->update(['component_set_id' => $setId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('components', function (Blueprint $table) {
            $table->dropColumn('component_set_id');
        });

        Schema::dropIfExists('component_sets');
    }
};
