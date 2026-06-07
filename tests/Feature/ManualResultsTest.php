<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\Qualification;
use App\Models\ExamSeries;
use App\Models\Subject;
use App\Models\Candidate;
use App\Models\CandidateEnrollment;
use App\Models\SubjectResult;
use App\Models\Component;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ManualResultsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected School $school;
    protected Qualification $gceQual;
    protected ExamSeries $series;
    protected Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'school_name' => 'Lucky International School',
            'school_code' => 'CHS001'
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

        $this->gceQual = Qualification::create([
            'qualification_type' => 'AS_A_LEVEL',
            'qualification_name' => 'GCE AS and A Level'
        ]);

        $this->series = ExamSeries::create([
            'series_code' => 'JUN-2026',
            'year' => 2026,
            'month' => 'June',
            'is_active' => true
        ]);

        $this->subject = Subject::create([
            'subject_code' => '9709',
            'subject_name' => 'Mathematics',
            'qualification_id' => $this->gceQual->id,
            'total_marks' => 200,
            'passing_percentage' => 40.00
        ]);
    }

    public function test_components_default_to_applicable_when_no_result()
    {
        $comp1 = Component::create([
            'subject_id' => $this->subject->id,
            'component_code' => 'P1',
            'component_name' => 'Pure Mathematics 1',
            'total_marks' => 75,
            'component_type' => 'paper'
        ]);

        $candidate = Candidate::create([
            'candidate_number' => '0002',
            'candidate_name' => 'Bob Smith',
            'school_id' => $this->school->id,
            'enrollment_date' => now()->toDateString()
        ]);

        $enrollment = CandidateEnrollment::create([
            'candidate_id' => $candidate->id,
            'series_id' => $this->series->id,
            'qualification_id' => $this->gceQual->id,
            'subject_id' => null,
            'enrollment_status' => 'enrolled',
            'enrolled_date' => now()->toDateString()
        ]);

        CandidateEnrollment::create([
            'candidate_id' => $candidate->id,
            'series_id' => $this->series->id,
            'qualification_id' => $this->gceQual->id,
            'subject_id' => $this->subject->id,
            'enrollment_status' => 'enrolled',
            'enrolled_date' => now()->toDateString()
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/manual-results/{$this->series->id}/{$this->subject->id}");

        $response->assertStatus(200);
        
        // Assert that the view has $rows and applicable is true
        $rows = $response->viewData('rows');
        $this->assertCount(1, $rows);
        $this->assertTrue($rows[0]['components'][0]['applicable']);
    }

    public function test_candidates_are_sorted_by_candidate_number()
    {
        $candidate1 = Candidate::create([
            'candidate_number' => '0003',
            'candidate_name' => 'Charlie',
            'school_id' => $this->school->id,
            'enrollment_date' => now()->toDateString()
        ]);

        $candidate2 = Candidate::create([
            'candidate_number' => '0001',
            'candidate_name' => 'Alice',
            'school_id' => $this->school->id,
            'enrollment_date' => now()->toDateString()
        ]);

        CandidateEnrollment::create([
            'candidate_id' => $candidate1->id,
            'series_id' => $this->series->id,
            'qualification_id' => $this->gceQual->id,
            'subject_id' => null,
            'enrollment_status' => 'enrolled',
            'enrolled_date' => now()->toDateString()
        ]);

        CandidateEnrollment::create([
            'candidate_id' => $candidate1->id,
            'series_id' => $this->series->id,
            'qualification_id' => $this->gceQual->id,
            'subject_id' => $this->subject->id,
            'enrollment_status' => 'enrolled',
            'enrolled_date' => now()->toDateString()
        ]);

        CandidateEnrollment::create([
            'candidate_id' => $candidate2->id,
            'series_id' => $this->series->id,
            'qualification_id' => $this->gceQual->id,
            'subject_id' => null,
            'enrollment_status' => 'enrolled',
            'enrolled_date' => now()->toDateString()
        ]);

        CandidateEnrollment::create([
            'candidate_id' => $candidate2->id,
            'series_id' => $this->series->id,
            'qualification_id' => $this->gceQual->id,
            'subject_id' => $this->subject->id,
            'enrollment_status' => 'enrolled',
            'enrolled_date' => now()->toDateString()
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/manual-results/{$this->series->id}/{$this->subject->id}");

        $response->assertStatus(200);
        $rows = $response->viewData('rows')->values();
        $this->assertEquals('0001', $rows[0]['candidate_no']);
        $this->assertEquals('0003', $rows[1]['candidate_no']);
    }

    public function test_can_save_lowercase_as_level_grade()
    {
        $candidate = Candidate::create([
            'candidate_number' => '0001',
            'candidate_name' => 'Alice',
            'school_id' => $this->school->id,
            'enrollment_date' => now()->toDateString()
        ]);

        $enrollment = CandidateEnrollment::create([
            'candidate_id' => $candidate->id,
            'series_id' => $this->series->id,
            'qualification_id' => $this->gceQual->id,
            'subject_id' => null,
            'enrollment_status' => 'enrolled',
            'enrolled_date' => now()->toDateString()
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/manual-results/{$this->series->id}/{$this->subject->id}/save-row", [
                'enrollment_id' => $enrollment->id,
                'grade' => 'a',
                'pum' => 85
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('subject_results', [
            'enrollment_id' => $enrollment->id,
            'grade' => 'a',
            'pum' => 85,
            'is_passed' => true
        ]);
    }

    public function test_results_index_displays_grouped_subject_wise_summary()
    {
        $candidate = Candidate::create([
            'candidate_number' => '0001',
            'candidate_name' => 'Alice',
            'school_id' => $this->school->id,
            'enrollment_date' => now()->toDateString()
        ]);

        $enrollment = CandidateEnrollment::create([
            'candidate_id' => $candidate->id,
            'series_id' => $this->series->id,
            'qualification_id' => $this->gceQual->id,
            'subject_id' => null,
            'enrollment_status' => 'enrolled',
            'enrolled_date' => now()->toDateString()
        ]);

        SubjectResult::create([
            'enrollment_id' => $enrollment->id,
            'subject_id' => $this->subject->id,
            'series_id' => $this->series->id,
            'grade' => 'a',
            'pum' => 85,
            'is_passed' => true,
            'uploaded_by' => $this->admin->id
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/results/series/{$this->series->id}");

        $response->assertStatus(200);
        $response->assertSee('Series Overview');
        $response->assertSee($this->subject->subject_name);
        // Average PUM (85%) should be visible in the subject card
        $response->assertSee('85%');
    }

    public function test_can_view_subject_details_candidate_list()
    {
        $candidate = Candidate::create([
            'candidate_number' => '0001',
            'candidate_name' => 'Alice',
            'school_id' => $this->school->id,
            'enrollment_date' => now()->toDateString()
        ]);

        $enrollment = CandidateEnrollment::create([
            'candidate_id' => $candidate->id,
            'series_id' => $this->series->id,
            'qualification_id' => $this->gceQual->id,
            'subject_id' => null,
            'enrollment_status' => 'enrolled',
            'enrolled_date' => now()->toDateString()
        ]);

        SubjectResult::create([
            'enrollment_id' => $enrollment->id,
            'subject_id' => $this->subject->id,
            'series_id' => $this->series->id,
            'grade' => 'a',
            'pum' => 85,
            'is_passed' => true,
            'uploaded_by' => $this->admin->id
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/results/subject/{$this->series->id}/{$this->subject->id}");

        $response->assertStatus(200);
        $response->assertSee('Alice');
        $response->assertSee('0001');
    }

    public function test_can_delete_subject_result_and_redirect_properly()
    {
        $candidate = Candidate::create([
            'candidate_number' => '0001',
            'candidate_name' => 'Alice',
            'school_id' => $this->school->id,
            'enrollment_date' => now()->toDateString()
        ]);

        $enrollment = CandidateEnrollment::create([
            'candidate_id' => $candidate->id,
            'series_id' => $this->series->id,
            'qualification_id' => $this->gceQual->id,
            'subject_id' => null,
            'enrollment_status' => 'enrolled',
            'enrolled_date' => now()->toDateString()
        ]);

        $result = SubjectResult::create([
            'enrollment_id' => $enrollment->id,
            'subject_id' => $this->subject->id,
            'series_id' => $this->series->id,
            'grade' => 'a',
            'pum' => 85,
            'is_passed' => true,
            'uploaded_by' => $this->admin->id
        ]);

        // Test delete with referrer as show page
        $response = $this->actingAs($this->admin)
            ->from(route('results.show', $result->id))
            ->delete("/results/{$result->id}");

        $response->assertRedirect(route('results.index'));
        $this->assertDatabaseMissing('subject_results', ['id' => $result->id]);
    }

    public function test_can_delete_all_results_of_subject_in_series()
    {
        $candidate1 = Candidate::create([
            'candidate_number' => '0001',
            'candidate_name' => 'Alice',
            'school_id' => $this->school->id,
            'enrollment_date' => now()->toDateString()
        ]);

        $candidate2 = Candidate::create([
            'candidate_number' => '0002',
            'candidate_name' => 'Bob',
            'school_id' => $this->school->id,
            'enrollment_date' => now()->toDateString()
        ]);

        $enrollment1 = CandidateEnrollment::create([
            'candidate_id' => $candidate1->id,
            'series_id' => $this->series->id,
            'qualification_id' => $this->gceQual->id,
            'subject_id' => null,
            'enrollment_status' => 'enrolled',
            'enrolled_date' => now()->toDateString()
        ]);

        $enrollment2 = CandidateEnrollment::create([
            'candidate_id' => $candidate2->id,
            'series_id' => $this->series->id,
            'qualification_id' => $this->gceQual->id,
            'subject_id' => null,
            'enrollment_status' => 'enrolled',
            'enrolled_date' => now()->toDateString()
        ]);

        $result1 = SubjectResult::create([
            'enrollment_id' => $enrollment1->id,
            'subject_id' => $this->subject->id,
            'series_id' => $this->series->id,
            'grade' => 'a',
            'pum' => 85,
            'is_passed' => true,
            'uploaded_by' => $this->admin->id
        ]);

        $result2 = SubjectResult::create([
            'enrollment_id' => $enrollment2->id,
            'subject_id' => $this->subject->id,
            'series_id' => $this->series->id,
            'grade' => 'b',
            'pum' => 75,
            'is_passed' => true,
            'uploaded_by' => $this->admin->id
        ]);

        $response = $this->actingAs($this->admin)
            ->delete("/results/subject/{$this->series->id}/{$this->subject->id}");

        $response->assertRedirect(route('results.index'));
        $this->assertDatabaseMissing('subject_results', ['id' => $result1->id]);
        $this->assertDatabaseMissing('subject_results', ['id' => $result2->id]);
    }

    public function test_broadsheet_view_renders_correctly()
    {
        $candidate = Candidate::create([
            'candidate_number' => '0001',
            'candidate_name' => 'Alice',
            'school_id' => $this->school->id,
            'enrollment_date' => now()->toDateString()
        ]);

        $enrollment = CandidateEnrollment::create([
            'candidate_id' => $candidate->id,
            'series_id' => $this->series->id,
            'qualification_id' => $this->gceQual->id,
            'subject_id' => null,
            'enrollment_status' => 'enrolled',
            'enrolled_date' => now()->toDateString()
        ]);

        SubjectResult::create([
            'enrollment_id' => $enrollment->id,
            'subject_id' => $this->subject->id,
            'series_id' => $this->series->id,
            'grade' => 'A*',
            'pum' => 95,
            'is_passed' => true,
            'uploaded_by' => $this->admin->id
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/results/broadsheet/{$this->series->id}/{$this->gceQual->id}");

        $response->assertStatus(200);
        $response->assertSee('Broadsheet View');
        $response->assertSee('Alice');
        $response->assertSee('0001');
    }

    public function test_can_toggle_subject_registration()
    {
        $candidate = Candidate::create([
            'candidate_number' => '0005',
            'candidate_name' => 'Eve',
            'school_id' => $this->school->id,
            'enrollment_date' => now()->toDateString()
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/student-entries/{$this->series->id}/toggle-subject", [
                'candidate_id' => $candidate->id,
                'subject_id' => $this->subject->id,
                'qualification_id' => $this->gceQual->id,
                'registered' => 1
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('candidate_enrollments', [
            'candidate_id' => $candidate->id,
            'series_id' => $this->series->id,
            'subject_id' => $this->subject->id,
        ]);
    }
}
