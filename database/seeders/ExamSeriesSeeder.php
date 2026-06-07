<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamSeries;

class ExamSeriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $months = ['March', 'June', 'November'];

        for ($year = 2018; $year <= 2030; $year++) {
            foreach ($months as $month) {
                $monthCode = strtoupper(substr($month, 0, 3));
                $seriesCode = "{$monthCode}-{$year}";

                ExamSeries::updateOrCreate(
                    [
                        'year' => $year,
                        'month' => $month,
                    ],
                    [
                        'series_code' => $seriesCode,
                        'series_name' => "{$month} {$year}",
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
