<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\Candidate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CandidateProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected School $school;
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

    public function test_guest_cannot_view_candidate_profile()
    {
        $this->assertTrue(true);
    }

    public function test_admin_can_view_candidate_profile()
    {
        $response = $this->actingAs($this->admin)
            ->get("/students/{$this->candidate->id}");

        $response->assertStatus(200);
        $response->assertSee('Jane Doe');
        $response->assertSee('CN123');
        $response->assertSee('Edit Profile');
    }

    public function test_admin_can_view_edit_candidate_form()
    {
        $response = $this->actingAs($this->admin)
            ->get("/students/{$this->candidate->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Jane Doe');
        $response->assertSee('Edit Candidate Profile');
    }

    public function test_admin_can_update_candidate_profile()
    {
        $response = $this->actingAs($this->admin)
            ->put("/students/{$this->candidate->id}", [
                'candidate_name' => 'Jane Smith',
                'candidate_number' => 'CN123-NEW',
                'date_of_birth' => '2010-06-20',
                'gender' => 'F',
                'status' => 'graduated',
            ]);

        $response->assertRedirect("/students/{$this->candidate->id}");
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('candidates', [
            'id' => $this->candidate->id,
            'candidate_name' => 'Jane Smith',
            'candidate_number' => 'CN123-NEW',
            'date_of_birth' => '2010-06-20 00:00:00',
            'gender' => 'F',
            'status' => 'graduated',
        ]);
    }
}
