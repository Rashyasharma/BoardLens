<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('component_marks', 'grade')) {
            Schema::table('component_marks', function (Blueprint $table) {
                // Component-level grade (e.g. A, B, C for an individual paper)
                $table->string('grade')->nullable()->after('percentage');
            });
        }
    }

    public function down(): void
    {
        Schema::table('component_marks', function (Blueprint $table) {
            $table->dropColumn('grade');
        });
    }
};
