<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\Candidate;
use App\Models\Qualification;
use App\Models\Subject;
use App\Models\ExamSeries;
use App\Models\CandidateEnrollment;
use App\Models\SubjectResult;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StudentJourneyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected School $school;
    protected Candidate $candidate;
    protected ExamSeries $series;
    protected Qualification $qual;
    protected Subject $subject;
    protected CandidateEnrollment $enrollment;
    protected SubjectResult $result;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'school_name' => 'Lucky International School',
            'school_code' => 'LIS001'
        ]);

        $this->admin = User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@cep.local',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'school_id' => $this->school->id,
            'is_active' => true
        ]);

        $this->candidate = Candidate::create([
            'candidate_number' => 'CN555',
            'candidate_name' => 'Alice Cooper',
            'school_id' => $this->school->id,
            'date_of_birth' => '2011-03-21',
            'gender' => 'F',
            'enrollment_date' => '2025-01-01',
            'status' => 'active'
        ]);

        $this->series = ExamSeries::create([
            'series_code' => 'JUN25',
            'year' => 2025,
            'month' => 'June',
            'deadline_for_entry' => '2025-02-28',
            'result_publication_date' => '2025-08-15',
            'is_active' => true
        ]);

        $this->qual = Qualification::create([
            'qualification_type' => 'IGCSE',
            'qualification_name' => 'IGCSE'
        ]);

        $this->subject = Subject::create([
            'subject_code' => '0450',
            'subject_name' => 'Business Studies',
            'qualification_id' => $this->qual->id,
            'total_marks' => 100,
            'passing_percentage' => 40.00
        ]);

        $this->enrollment = CandidateEnrollment::create([
            'candidate_id' => $this->candidate->id,
            'series_id' => $this->series->id,
            'qualification_id' => $this->qual->id,
            'subject_id' => $this->subject->id,
            'enrollment_status' => 'enrolled',
            'enrolled_date' => '2025-01-15'
        ]);

        $this->result = SubjectResult::create([
            'enrollment_id' => $this->enrollment->id,
            'subject_id' => $this->subject->id,
            'series_id' => $this->series->id,
            'grade' => 'A',
            'pum' => 85.00,
            'total_obtained_marks' => 85,
            'total_marks' => 100,
            'overall_percentage' => 85.00,
            'calculated_uniform_mark' => 85.00,
            'is_passed' => true,
            'uploaded_by' => $this->admin->id
        ]);
    }

    public function test_guest_cannot_access_student_journey()
    {
        $response = $this->get('/analysis/student-journey');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_access_student_journey_page()
    {
        $response = $this->actingAs($this->admin)->get('/analysis/student-journey');
        $response->assertStatus(200);
        $response->assertSee('Select Candidate Journey');
        $response->assertSee('Alice Cooper');
    }

    public function test_admin_can_visualize_specific_student_journey()
    {
        $response = $this->actingAs($this->admin)->get('/analysis/student-journey?candidate_id=' . $this->candidate->id);
        $response->assertStatus(200);
        $response->assertSee('Alice Cooper');
        $response->assertSee('Business Studies');
        $response->assertSee('Average PUM: 85%');
        $response->assertSee('Baseline Performance Established');
    }

    public function test_admin_can_visualize_student_journey_by_name()
    {
        // Create another candidate with same name but different candidate number
        $candidate2 = Candidate::create([
            'candidate_number' => 'CN666',
            'candidate_name' => 'Alice Cooper',
            'school_id' => $this->school->id,
            'date_of_birth' => '2011-03-21',
            'gender' => 'F',
            'enrollment_date' => '2026-01-01',
            'status' => 'active'
        ]);

        $series2 = ExamSeries::create([
            'series_code' => 'JUN26',
            'year' => 2026,
            'month' => 'June',
            'deadline_for_entry' => '2026-02-28',
            'result_publication_date' => '2026-08-15',
            'is_active' => true
        ]);

        $enrollment2 = CandidateEnrollment::create([
            'candidate_id' => $candidate2->id,
            'series_id' => $series2->id,
            'qualification_id' => $this->qual->id,
            'subject_id' => $this->subject->id,
            'enrollment_status' => 'enrolled',
            'enrolled_date' => '2026-01-15'
        ]);

        $result2 = SubjectResult::create([
            'enrollment_id' => $enrollment2->id,
            'subject_id' => $this->subject->id,
            'series_id' => $series2->id,
            'grade' => 'A*',
            'pum' => 95.00,
            'total_obtained_marks' => 95,
            'total_marks' => 100,
            'overall_percentage' => 95.00,
            'calculated_uniform_mark' => 95.00,
            'is_passed' => true,
            'uploaded_by' => $this->admin->id
        ]);

        $response = $this->actingAs($this->admin)->get('/analysis/student-journey?candidate_name=' . urlencode('Alice Cooper'));
        $response->assertStatus(200);
        $response->assertSee('Alice Cooper');
        $response->assertSee('CN555, CN666'); // Shows both numbers in header
        $response->assertSee('Average PUM: 95%'); // Displays second series PUM
        $response->assertSee('Average PUM: 85%'); // Displays first series PUM
        $response->assertSee('Positive Growth Trajectory'); // Displays growth trend insight between series
    }
}
