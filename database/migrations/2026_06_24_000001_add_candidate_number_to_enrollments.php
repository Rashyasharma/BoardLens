<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('candidate_enrollments', function (Blueprint $table) {
            $table->string('candidate_number')->nullable()->after('series_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidate_enrollments', function (Blueprint $table) {
            $table->dropColumn('candidate_number');
        });
    }
};
