<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\Qualification;
use App\Models\ExamSeries;
use App\Models\Candidate;
use App\Models\CandidateEnrollment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class StudentEntryAddCandidatesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected School $school;
    protected Qualification $igcseQual;
    protected Qualification $gceQual;
    protected ExamSeries $series;
    protected Candidate $candidate;

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

        $this->igcseQual = Qualification::create([
            'qualification_type' => 'IGCSE',
            'qualification_name' => 'IGCSE'
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

        $this->candidate = Candidate::create([
            'candidate_number' => 'CN123',
            'candidate_name' => 'Jane Doe',
            'school_id' => $this->school->id,
            'date_of_birth' => '2010-05-15',
            'gender' => 'F',
            'enrollment_date' => '2025-09-01',
            'status' => 'active'
        ]);
    }

    public function test_guest_cannot_access_manage_entries_page()
    {
        $response = $this->get("/student-entries/{$this->series->id}");
        $response->assertRedirect('/login');
    }

    public function test_admin_can_access_manage_entries_page()
    {
        $response = $this->actingAs($this->admin)
            ->get("/student-entries/{$this->series->id}");

        $response->assertStatus(200);
        $response->assertSee('Manage Registered Candidates');
        $response->assertSee('IGCSE Candidates');
        $response->assertSee('GCE AS & A Level Candidates');
    }


    public function test_admin_can_upload_candidates_manually_comma_separated()
    {
        $response = $this->actingAs($this->admin)
            ->post("/student-entries/{$this->series->id}/upload", [
                'qualification_id' => $this->igcseQual->id,
                'raw_text' => "IG001, John Doe\nIG002, Jane Smith",
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', '2 candidates successfully imported/enrolled.');

        $this->assertDatabaseHas('candidates', [
            'candidate_number' => 'IG001',
            'candidate_name' => 'John Doe',
        ]);

        $this->assertDatabaseHas('candidate_enrollments', [
            'series_id' => $this->series->id,
            'qualification_id' => $this->igcseQual->id,
            'subject_id' => null
        ]);
    }

    public function test_admin_can_upload_candidates_via_csv()
    {
        $csvContent = "candidate_number,candidate_name\nCSV001,Charlie Brown\nCSV002,David Beckham\n";
        $file = UploadedFile::fake()->createWithContent('candidates.csv', $csvContent);

        $response = $this->actingAs($this->admin)
            ->post("/student-entries/{$this->series->id}/upload", [
                'qualification_id' => $this->gceQual->id,
                'candidate_file' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', '2 candidates successfully imported/enrolled.');

        $this->assertDatabaseHas('candidates', [
            'candidate_number' => 'CSV001',
            'candidate_name' => 'Charlie Brown',
        ]);

        $this->assertDatabaseHas('candidate_enrollments', [
            'series_id' => $this->series->id,
            'qualification_id' => $this->gceQual->id,
            'subject_id' => null
        ]);
    }

    public function test_admin_can_unenroll_candidate()
    {
        // Setup enrollment
        $enrollment = CandidateEnrollment::create([
            'candidate_id' => $this->candidate->id,
            'series_id' => $this->series->id,
            'qualification_id' => $this->igcseQual->id,
            'subject_id' => null,
            'enrollment_status' => 'enrolled',
            'enrolled_date' => now()->toDateString()
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/student-entries/{$this->series->id}/unenroll/{$this->candidate->id}", [
                'qualification_id' => $this->igcseQual->id
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Candidate removed from this series.');

        $this->assertDatabaseMissing('candidate_enrollments', [
            'id' => $enrollment->id
        ]);
    }
}
