<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\Qualification;
use App\Models\ExamSeries;
use App\Models\Subject;
use App\Models\Candidate;
use App\Models\SubjectResult;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UploadMarkTest extends TestCase
{
    use RefreshDatabase;

    protected User $examOfficer;
    protected School $school;
    protected ExamSeries $series;
    protected Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->school = School::create([
            'school_name' => 'Test School',
            'school_code' => 'TST001'
        ]);

        $this->examOfficer = User::create([
            'name' => 'Exam Officer',
            'username' => 'officer_test',
            'email' => 'officer_test@cep.local',
            'password' => 'password',
            'role' => 'exam_officer',
            'school_id' => $this->school->id
        ]);

        $qual = Qualification::create([
            'qualification_type' => 'IGCSE',
            'qualification_name' => 'IGCSE'
        ]);

        $this->series = ExamSeries::create([
            'series_code' => 'MAR-2026',
            'year' => 2026,
            'month' => 'March',
            'series_name' => 'March 2026'
        ]);

        $this->subject = Subject::create([
            'subject_code' => '0580',
            'subject_name' => 'Mathematics',
            'qualification_id' => $qual->id,
            'total_marks' => 200,
            'passing_percentage' => 40.00
        ]);
    }

    /**
     * Test guest redirects to login page.
     */
    public function test_unauthenticated_user_redirected_to_login()
    {
        $response = $this->post('/results/upload');
        $response->assertRedirect('/login');
    }

    /**
     * Test invalid file uploads are rejected by the validator.
     */
    public function test_invalid_results_file_rejected()
    {
        // pdf is not a valid format
        $invalidFile = UploadedFile::fake()->create('results.pdf', 10, 'application/pdf');

        $response = $this->actingAs($this->examOfficer)
            ->post('/results/upload', [
                'results_file' => $invalidFile,
                'qualification_id' => $this->subject->qualification_id,
                'series_id' => $this->series->id,
                'subject_id' => $this->subject->id,
            ]);

        $response->assertStatus(302); // Laravel redirect back
        $response->assertSessionHasErrors('results_file');
    }

    /**
     * Test Phase 1 results upload via CSV.
     */
    public function test_successful_phase_1_results_upload()
    {
        // Create candidate
        $candidate = Candidate::create([
            'candidate_number' => 'CN111',
            'candidate_name' => 'John Doe',
            'school_id' => $this->school->id,
            'enrollment_date' => now()->toDateString()
        ]);

        $csvContent = "Candidate Number,Student Name,Grade,PUM\n" .
                      "CN111,John Doe,A,82.00\n";
        
        $csvFile = UploadedFile::fake()->createWithContent('results.csv', $csvContent);

        $response = $this->actingAs($this->examOfficer)
            ->post('/results/upload', [
                'results_file' => $csvFile,
                'qualification_id' => $this->subject->qualification_id,
                'series_id' => $this->series->id,
                'subject_id' => $this->subject->id,
            ], [
                'X-Requested-With' => 'XMLHttpRequest'
            ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'message' => 'Results uploaded successfully',
            'successful_count' => 1,
            'failed_count' => 0
        ]);

        $this->assertDatabaseHas('subject_results', [
            'grade' => 'A',
            'pum' => 82.00,
            'status' => 'pending_components'
        ]);
    }

    /**
     * Test displaying component marks upload selector page.
     */
    public function test_exam_officer_can_view_component_upload_selector()
    {
        $response = $this->actingAs($this->examOfficer)
            ->get('/uploads/components');

        $response->assertStatus(200);
        $response->assertSee('Select Subject');
    }

    /**
     * Test displaying component marks upload form with params.
     */
    public function test_exam_officer_can_view_component_upload_form_with_params()
    {
        $response = $this->actingAs($this->examOfficer)
            ->get("/uploads/components?series_id={$this->series->id}&subject_id={$this->subject->id}");

        $response->assertStatus(200);
        $response->assertSee('Upload Component Marks (Phase 2)');
        $response->assertSee($this->subject->subject_name);
    }
}
