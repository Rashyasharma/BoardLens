<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Qualification;

class QualificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Qualification::updateOrCreate(
            ['qualification_type' => 'IGCSE'],
            [
                'qualification_name' => 'IGCSE',
                'description' => 'International General Certificate of Secondary Education'
            ]
        );

        Qualification::updateOrCreate(
            ['qualification_type' => 'AS_A_LEVEL'],
            [
                'qualification_name' => 'GCE AS and A Level',
                'description' => 'General Certificate of Education Advanced Subsidiary and Advanced Level'
            ]
        );
    }
}
