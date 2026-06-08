<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CbseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Qualifications ───────────────────────────────────────────────────
        $class10Id = (string) Str::uuid();
        $class12Id = (string) Str::uuid();

        DB::table('cbse_qualifications')->insertOrIgnore([
            [
                'id'                 => $class10Id,
                'qualification_type' => 'CLASS_10',
                'qualification_name' => 'Secondary (Class 10)',
                'board_code'         => '241',
                'description'        => 'CBSE Secondary School Certificate (SSC) — Class 10 board examination.',
                'is_active'          => true,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'id'                 => $class12Id,
                'qualification_type' => 'CLASS_12',
                'qualification_name' => 'Senior Secondary (Class 12)',
                'board_code'         => '301',
                'description'        => 'CBSE Senior Secondary Certificate (HSSC) — Class 12 board examination.',
                'is_active'          => true,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
        ]);

        // ── Class 10 Subjects ─────────────────────────────────────────────────
        $class10Subjects = [
            ['002', 'Hindi Course A',            80, 20, 'Internal Assessment'],
            ['003', 'Hindi Course B',             80, 20, 'Internal Assessment'],
            ['006', 'Sanskrit',                   80, 20, 'Internal Assessment'],
            ['010', 'English Language & Literature', 80, 20, 'Internal Assessment'],
            ['041', 'Mathematics (Standard)',     80, 20, 'Internal Assessment'],
            ['241', 'Mathematics (Basic)',        80, 20, 'Internal Assessment'],
            ['086', 'Science',                    80, 20, 'Practical'],
            ['087', 'Social Science',             80, 20, 'Internal Assessment'],
            ['165', 'Computer Applications',     50, 50, 'Practical'],
            ['154', 'Artificial Intelligence',   50, 50, 'Practical'],
            ['064', 'Home Science',              60, 40, 'Practical'],
            ['122', 'Urdu Course A',              80, 20, 'Internal Assessment'],
            ['303', 'Urdu Course B',              80, 20, 'Internal Assessment'],
            ['048', 'Physical Education',         50, 50, 'Practical'],
            ['049', 'Painting',                   40, 60, 'Practical'],
        ];

        foreach ($class10Subjects as [$code, $name, $theory, $practical, $practicalType]) {
            DB::table('cbse_subjects')->insertOrIgnore([
                'id'                   => (string) Str::uuid(),
                'qualification_id'     => $class10Id,
                'subject_code'         => $code,
                'subject_name'         => $name,
                'theory_marks'         => $theory,
                'practical_marks'      => $practical,
                'practical_type'       => $practicalType,
                'passing_percentage'   => 33.00,
                'theory_passing_marks' => round($theory * 0.33, 2),
                'is_active'            => true,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }

        // ── Class 12 Subjects ─────────────────────────────────────────────────
        $class12Subjects = [
            ['301', 'English Core',          80, 20, 'Internal Assessment'],
            ['302', 'Hindi Core',             80, 20, 'Internal Assessment'],
            ['303', 'Hindi Elective',         80, 20, 'Internal Assessment'],
            ['043', 'Mathematics',            80, 20, 'Internal Assessment'],
            ['042', 'Physics',                70, 30, 'Practical'],
            ['044', 'Chemistry',              70, 30, 'Practical'],
            ['045', 'Biology',                70, 30, 'Practical'],
            ['048', 'Physical Education',     70, 30, 'Practical'],
            ['029', 'Geography',              70, 30, 'Practical'],
            ['037', 'Psychology',             70, 30, 'Practical'],
            ['083', 'Computer Science',       70, 30, 'Practical'],
            ['065', 'Informatics Practices',  70, 30, 'Practical'],
            ['058', 'Home Science',           60, 40, 'Practical'],
            ['041', 'Economics',              80, 20, 'Project'],
            ['030', 'Accountancy',            80, 20, 'Project'],
            ['054', 'Business Studies',       80, 20, 'Project'],
            ['028', 'History',                80, 20, 'Project'],
            ['027', 'Political Science',      80, 20, 'Project'],
            ['039', 'Sociology',              80, 20, 'Project'],
            ['049', 'Painting',               40, 60, 'Practical'],
            ['318', 'Entrepreneurship',       70, 30, 'Project'],
            ['064', 'Sanskrit Core',          80, 20, 'Internal Assessment'],
            ['322', 'Sanskrit Elective',      80, 20, 'Internal Assessment'],
        ];

        foreach ($class12Subjects as [$code, $name, $theory, $practical, $practicalType]) {
            DB::table('cbse_subjects')->insertOrIgnore([
                'id'                   => (string) Str::uuid(),
                'qualification_id'     => $class12Id,
                'subject_code'         => $code,
                'subject_name'         => $name,
                'theory_marks'         => $theory,
                'practical_marks'      => $practical,
                'practical_type'       => $practicalType,
                'passing_percentage'   => 33.00,
                'theory_passing_marks' => round($theory * 0.33, 2),
                'is_active'            => true,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }
    }
}
