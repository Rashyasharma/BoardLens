<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\ComponentMarks;
use App\Models\CandidateEnrollment;
use App\Models\Component;
use App\Models\Subject;
use App\Models\Qualification;
use App\Models\ExamSeries;
use App\Models\School;
use App\Models\User;
use App\Models\SubjectResult;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MarkCalculationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test grade and PUM direct storage (Phase 1) and component mark updates (Phase 2).
     */
    public function test_two_phase_results_and_component_calculation()
    {
        // 1. Create School
        $school = School::create([
            'school_name' => 'Test School',
            'school_code' => 'TST001'
        ]);

        // 2. Create User
        $officer = User::create([
            'name' => 'Exam Officer',
            'username' => 'officer_test',
            'email' => 'officer_test@cep.local',
            'password' => 'password',
            'role' => 'exam_officer',
            'school_id' => $school->id
        ]);

        // 3. Create Qualification
        $qual = Qualification::create([
            'qualification_type' => 'IGCSE',
            'qualification_name' => 'IGCSE'
        ]);

        // 4. Create Series with split month/year
        $series = ExamSeries::create([
            'series_code' => 'TST_SR',
            'qualification_id' => $qual->id,
            'year' => 2026,
            'month' => 'March',
            'series_name' => 'March 2026'
        ]);

        // 5. Create Subject
        $subject = Subject::create([
            'subject_code' => '0580',
            'subject_name' => 'Mathematics',
            'qualification_id' => $qual->id,
            'total_marks' => 200,
            'passing_percentage' => 40.00
        ]);

        // 6. Create Candidate
        $cand = \App\Models\Candidate::create([
            'candidate_number' => 'CN999',
            'candidate_name' => 'Test Candidate',
            'school_id' => $school->id,
            'enrollment_date' => now()->toDateString()
        ]);

        // 7. Enroll Candidate
        $enrollment = CandidateEnrollment::create([
            'candidate_id' => $cand->id,
            'series_id' => $series->id,
            'qualification_id' => $qual->id,
            'subject_id' => $subject->id,
            'enrolled_date' => now()->toDateString()
        ]);

        // 8. Create Components (P1: 100 max, P2: 100 max)
        $comp1 = Component::create([
            'subject_id' => $subject->id,
            'component_code' => 'P1',
            'component_name' => 'Paper 1',
            'component_type' => 'paper',
            'total_marks' => 100,
            'scaling_factor' => 5
        ]);

        $comp2 = Component::create([
            'subject_id' => $subject->id,
            'component_code' => 'P2',
            'component_name' => 'Paper 2',
            'component_type' => 'paper',
            'total_marks' => 100,
            'scaling_factor' => 5
        ]);

        // PHASE 1: Store Direct Result (Grade + PUM)
        $result = SubjectResult::create([
            'enrollment_id' => $enrollment->id,
            'subject_id' => $subject->id,
            'series_id' => $series->id,
            'grade' => 'A',
            'pum' => 82.50,
            'status' => 'pending_components',
            'uploaded_by' => $officer->id,
        ]);

        $this->assertEquals('A', $result->grade);
        $this->assertEquals(82.50, $result->pum);
        $this->assertEquals('pending_components', $result->status);
        $this->assertNull($result->total_obtained_marks);

        // PHASE 2: Upload Component Marks (Parallel)
        ComponentMarks::create([
            'subject_result_id' => $result->id,
            'enrollment_id' => $enrollment->id,
            'component_id' => $comp1->id,
            'obtained_marks' => 80.00,
            'total_marks' => 100,
            'uploaded_by' => $officer->id,
        ]);

        // Should check status is still pending_components (needs P2 marks)
        $result->load('componentMarks');
        $this->assertFalse($result->hasAllComponentsUploaded());

        ComponentMarks::create([
            'subject_result_id' => $result->id,
            'enrollment_id' => $enrollment->id,
            'component_id' => $comp2->id,
            'obtained_marks' => 90.00,
            'total_marks' => 100,
            'uploaded_by' => $officer->id,
        ]);

        // Now both components uploaded
        $result->load('componentMarks');
        $this->assertTrue($result->hasAllComponentsUploaded());

        // Perform calculation
        $result->calculateFromComponents();

        // Refresh from DB
        $result->refresh();

        $this->assertEquals('component_marks_added', $result->status);
        $this->assertEquals(170.00, $result->total_obtained_marks); // 80 + 90
        $this->assertEquals(200, $result->total_marks);
        $this->assertEquals(85.00, $result->overall_percentage); // (170/200)*100
        $this->assertEquals(85.00, $result->calculated_uniform_mark);
    }
}
