<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\User;
use App\Models\ExamSeries;
use App\Models\Qualification;
use App\Models\Subject;
use App\Models\GradeThreshold;
use App\Models\Candidate;
use App\Models\CandidateEnrollment;
use App\Models\ComponentMarks;
use App\Models\SubjectResult;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create School
        $school = School::create([
            'school_name' => 'Lucky International School',
            'school_code' => 'CHS001',
            'address' => '123 Educational Way, Cambridge',
            'contact_email' => 'admin@chs.edu',
            'contact_phone' => '+44 1223 123456'
        ]);

        // 2. Create Single Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@cep.local',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'school_id' => $school->id,
            'is_active' => true
        ]);

        $qualifications = [
            'IGCSE' => Qualification::where('qualification_type', 'IGCSE')->first(),
            'AS_A_LEVEL' => Qualification::where('qualification_type', 'AS_A_LEVEL')->first(),
        ];
        $igcse = $qualifications['IGCSE'];

        // 3. Create Exam Series
        $series = ExamSeries::updateOrCreate(
            ['series_code' => 'MAR-2026'],
            [
                'year' => 2026,
                'month' => 'March',
                'series_name' => 'March 2026',
                'deadline_for_entry' => '2026-01-15',
                'result_publication_date' => '2026-05-15',
                'is_active' => true
            ]
        );

        // 4. Create Grade Thresholds (purely reference)
        $math = Subject::where('subject_code', '0580')->first();
        $phys = Subject::where('subject_code', '0625')->first();

        // Grade thresholds for Math (0580)
        $mathGrades = [
            'A*' => [85.00, 100.00],
            'A' => [75.00, 84.99],
            'B' => [65.00, 74.99],
            'C' => [55.00, 64.99],
            'D' => [45.00, 54.99],
            'E' => [35.00, 44.99],
        ];
        foreach ($mathGrades as $grade => $pumRange) {
            GradeThreshold::create([
                'series_id' => $series->id,
                'subject_id' => $math->id,
                'grade' => $grade,
                'qualification_type' => 'IGCSE',
                'minimum_pum' => $pumRange[0],
                'maximum_pum' => $pumRange[1],
            ]);
        }

        // Grade thresholds for Physics (0625)
        $physGrades = [
            'A*' => [80.00, 100.00],
            'A' => [70.00, 79.99],
            'B' => [60.00, 69.99],
            'C' => [50.00, 59.99],
            'D' => [40.00, 49.99],
            'E' => [30.00, 39.99],
        ];
        foreach ($physGrades as $grade => $pumRange) {
            GradeThreshold::create([
                'series_id' => $series->id,
                'subject_id' => $phys->id,
                'grade' => $grade,
                'qualification_type' => 'IGCSE',
                'minimum_pum' => $pumRange[0],
                'maximum_pum' => $pumRange[1],
            ]);
        }

        // 5. Candidates and results data
        $candidatesData = [
            // [number, name, dob, gender, math_grade, math_pum, math_p2, math_p4, phys_grade, phys_pum, phys_p2, phys_p4, phys_p6]
            ['CN001', 'Alice Smith', '2010-04-12', 'F', 'A*', 92, 68, 120, 'A*', 88, 38, 72, 36],
            ['CN002', 'Bob Johnson', '2010-09-22', 'M', 'D', 48, 30, 65, 'E', 38, 15, 30, 15],
            ['CN003', 'Charlie Brown', '2010-01-05', 'M', 'B', 71, 50, 92, 'B', 68, 27, 55, 26],
            ['CN004', 'Daisy Miller', '2010-11-30', 'F', 'A', 82, 58, 106, 'A', 78, 32, 62, 31],
            ['CN005', 'Ethan Hunt', '2010-07-15', 'M', 'C', 61, 43, 79, 'C', 58, 23, 47, 23],
        ];

        foreach ($candidatesData as $data) {
            $cand = Candidate::create([
                'candidate_number' => $data[0],
                'candidate_name' => $data[1],
                'school_id' => $school->id,
                'date_of_birth' => $data[2],
                'gender' => $data[3],
                'enrollment_date' => '2025-09-01',
                'status' => 'active'
            ]);

            // Math Enrollment
            $mathEnroll = CandidateEnrollment::create([
                'candidate_id' => $cand->id,
                'series_id' => $series->id,
                'qualification_id' => $igcse->id,
                'subject_id' => $math->id,
                'enrolled_date' => '2025-09-01',
                'enrollment_status' => 'enrolled'
            ]);

            // Physics Enrollment
            $physEnroll = CandidateEnrollment::create([
                'candidate_id' => $cand->id,
                'series_id' => $series->id,
                'qualification_id' => $igcse->id,
                'subject_id' => $phys->id,
                'enrolled_date' => '2025-09-01',
                'enrollment_status' => 'enrolled'
            ]);

            // Math Results (Phase 1)
            $mathResult = SubjectResult::create([
                'enrollment_id' => $mathEnroll->id,
                'subject_id' => $math->id,
                'series_id' => $series->id,
                'grade' => $data[4],
                'pum' => (float)$data[5],
                'status' => 'pending_components',
                'result_uploaded_at' => now(),
                'uploaded_by' => $admin->id,
            ]);

            // Math Component Marks (Phase 2)
            $p2Component = $math->components->where('component_code', 'P2')->first();
            $p4Component = $math->components->where('component_code', 'P4')->first();

            ComponentMarks::create([
                'subject_result_id' => $mathResult->id,
                'enrollment_id' => $mathEnroll->id,
                'component_id' => $p2Component->id,
                'obtained_marks' => $data[6],
                'total_marks' => 70,
                'uploaded_by' => $admin->id
            ]);

            ComponentMarks::create([
                'subject_result_id' => $mathResult->id,
                'enrollment_id' => $mathEnroll->id,
                'component_id' => $p4Component->id,
                'obtained_marks' => $data[7],
                'total_marks' => 130,
                'uploaded_by' => $admin->id
            ]);

            // Trigger component calculations
            $mathResult->calculateFromComponents();

            // Physics Results (Phase 1)
            $physResult = SubjectResult::create([
                'enrollment_id' => $physEnroll->id,
                'subject_id' => $phys->id,
                'series_id' => $series->id,
                'grade' => $data[8],
                'pum' => (float)$data[9],
                'status' => 'pending_components',
                'result_uploaded_at' => now(),
                'uploaded_by' => $admin->id,
            ]);

            // Physics Component Marks (Phase 2)
            $physP2 = $phys->components->where('component_code', 'P2')->first();
            $physP4 = $phys->components->where('component_code', 'P4')->first();
            $physP6 = $phys->components->where('component_code', 'P6')->first();

            ComponentMarks::create([
                'subject_result_id' => $physResult->id,
                'enrollment_id' => $physEnroll->id,
                'component_id' => $physP2->id,
                'obtained_marks' => $data[10],
                'total_marks' => 40,
                'uploaded_by' => $admin->id
            ]);

            ComponentMarks::create([
                'subject_result_id' => $physResult->id,
                'enrollment_id' => $physEnroll->id,
                'component_id' => $physP4->id,
                'obtained_marks' => $data[11],
                'total_marks' => 80,
                'uploaded_by' => $admin->id
            ]);

            ComponentMarks::create([
                'subject_result_id' => $physResult->id,
                'enrollment_id' => $physEnroll->id,
                'component_id' => $physP6->id,
                'obtained_marks' => $data[12],
                'total_marks' => 40,
                'uploaded_by' => $admin->id
            ]);

            // Trigger component calculations
            $physResult->calculateFromComponents();
        }
    }
}
