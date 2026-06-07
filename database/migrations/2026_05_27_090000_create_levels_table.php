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
        Schema::create('levels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->timestamps();
        });

        // Insert AS Level and A Level records
        $asLevelId = (string) Str::uuid();
        $aLevelId = (string) Str::uuid();

        DB::table('levels')->insert([
            [
                'id' => $asLevelId,
                'name' => 'AS Level',
                'code' => 'AS',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $aLevelId,
                'name' => 'A Level',
                'code' => 'A',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        Schema::table('components', function (Blueprint $table) {
            $table->foreignUuid('level_id')->nullable()->constrained('levels')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('components', function (Blueprint $table) {
            $table->dropColumn('level_id');
        });

        Schema::dropIfExists('levels');
    }
};
