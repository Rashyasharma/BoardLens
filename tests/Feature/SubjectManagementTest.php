<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\Qualification;
use App\Models\Subject;
use App\Models\Component;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SubjectManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected School $school;
    protected Qualification $qual;
    protected Subject $subject;
    protected Component $comp1;
    protected Component $comp2;

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

        $this->qual = Qualification::create([
            'qualification_type' => 'IGCSE',
            'qualification_name' => 'IGCSE'
        ]);

        $this->subject = Subject::create([
            'subject_code' => '0580',
            'subject_name' => 'Mathematics',
            'qualification_id' => $this->qual->id,
            'total_marks' => 200,
            'passing_percentage' => 40.00
        ]);

        $this->comp1 = Component::create([
            'subject_id' => $this->subject->id,
            'component_code' => 'P1',
            'component_name' => 'Paper 1',
            'component_type' => 'paper',
            'total_marks' => 100,
            'scaling_factor' => 1,
            'is_mandatory' => true
        ]);

        $this->comp2 = Component::create([
            'subject_id' => $this->subject->id,
            'component_code' => 'P2',
            'component_name' => 'Paper 2',
            'component_type' => 'paper',
            'total_marks' => 100,
            'scaling_factor' => 1,
            'is_mandatory' => true
        ]);
    }

    public function test_guest_cannot_access_subjects_index()
    {
        $response = $this->get('/subjects');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_access_subjects_index()
    {
        $response = $this->actingAs($this->admin)->get('/subjects');
        $response->assertStatus(200);
        $response->assertSee('Mathematics');
        $response->assertSee('0580');
        $response->assertSee('IGCSE');
    }

    public function test_admin_can_view_create_subject_form()
    {
        $response = $this->actingAs($this->admin)->get('/subjects/create');
        $response->assertStatus(200);
        $response->assertSee('New Subject Configuration');
    }

    public function test_admin_can_store_new_subject_with_components()
    {
        $response = $this->actingAs($this->admin)->post('/subjects', [
            'qualification_id' => $this->qual->id,
            'subject_code' => '0610',
            'subject_name' => 'Biology',
            'components' => [
                [
                    'code' => 'P1',
                    'name' => 'Paper 1 Theory',
                    'marks' => 80
                ],
                [
                    'code' => 'P2',
                    'name' => 'Paper 2 Practical',
                    'marks' => 120
                ]
            ]
        ]);

        $response->assertRedirect('/subjects');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('subjects', [
            'subject_code' => '0610',
            'subject_name' => 'Biology',
            'total_marks' => 200
        ]);

        $this->assertDatabaseHas('components', [
            'component_code' => 'P1',
            'component_name' => 'Paper 1 Theory',
            'total_marks' => 80
        ]);
    }

    public function test_admin_can_view_edit_subject_form()
    {
        $response = $this->actingAs($this->admin)->get("/subjects/{$this->subject->id}/edit");
        $response->assertStatus(200);
        $response->assertSee('Edit Subject Configuration');
        $response->assertSee('Mathematics');
    }

    public function test_admin_can_update_subject_and_components()
    {
        // We will:
        // 1. Edit comp1: change code to P1-MOD, name to Paper 1 Modified, marks to 120
        // 2. Delete comp2 by not sending it in components array
        // 3. Add a new comp3: code P3-ADD, name Paper 3 Added, marks 80
        // Total subject marks should become 200 (120 + 80)
        
        $response = $this->actingAs($this->admin)->put("/subjects/{$this->subject->id}", [
            'subject_code' => '9709',
            'subject_name' => 'Mathematics Advanced',
            'components' => [
                [
                    'id' => $this->comp1->id,
                    'code' => 'P1-MOD',
                    'name' => 'Paper 1 Modified',
                    'marks' => 120
                ],
                [
                    'code' => 'P3-ADD',
                    'name' => 'Paper 3 Added',
                    'marks' => 80
                ]
            ]
        ]);

        $response->assertRedirect('/subjects');
        $response->assertSessionHas('success');

        // Verify subject updated
        $this->assertDatabaseHas('subjects', [
            'id' => $this->subject->id,
            'subject_name' => 'Mathematics Advanced',
            'total_marks' => 200
        ]);

        // Verify comp1 updated (including code!)
        $this->assertDatabaseHas('components', [
            'id' => $this->comp1->id,
            'component_code' => 'P1-MOD',
            'component_name' => 'Paper 1 Modified',
            'total_marks' => 120
        ]);

        // Verify comp2 deleted
        $this->assertDatabaseMissing('components', [
            'id' => $this->comp2->id
        ]);

        // Verify new component comp3 added
        $this->assertDatabaseHas('components', [
            'subject_id' => $this->subject->id,
            'component_code' => 'P3-ADD',
            'component_name' => 'Paper 3 Added',
            'total_marks' => 80
        ]);
    }

    public function test_admin_cannot_update_subject_with_duplicate_component_codes()
    {
        // Try submitting same code 'P1' for both components
        $response = $this->actingAs($this->admin)->put("/subjects/{$this->subject->id}", [
            'subject_code' => '9709',
            'subject_name' => 'Mathematics Advanced',
            'components' => [
                [
                    'id' => $this->comp1->id,
                    'code' => 'P1',
                    'name' => 'Paper 1 Modified',
                    'marks' => 120
                ],
                [
                    'code' => 'P1',
                    'name' => 'Paper 3 Added',
                    'marks' => 80
                ]
            ]
        ]);

        $response->assertSessionHasErrors('components');
    }

    public function test_admin_can_swap_component_codes()
    {
        // Swap P1 and P2 codes
        $response = $this->actingAs($this->admin)->put("/subjects/{$this->subject->id}", [
            'subject_code' => '0580',
            'subject_name' => 'Mathematics',
            'components' => [
                [
                    'id' => $this->comp1->id,
                    'code' => 'P2',
                    'name' => 'Paper 2 Swapped',
                    'marks' => 100
                ],
                [
                    'id' => $this->comp2->id,
                    'code' => 'P1',
                    'name' => 'Paper 1 Swapped',
                    'marks' => 100
                ]
            ]
        ]);

        $response->assertRedirect('/subjects');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('components', [
            'id' => $this->comp1->id,
            'component_code' => 'P2',
            'component_name' => 'Paper 2 Swapped'
        ]);

        $this->assertDatabaseHas('components', [
            'id' => $this->comp2->id,
            'component_code' => 'P1',
            'component_name' => 'Paper 1 Swapped'
        ]);
    }

    public function test_admin_can_delete_subject()
    {
        $response = $this->actingAs($this->admin)->delete("/subjects/{$this->subject->id}");
        $response->assertRedirect('/subjects');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('subjects', [
            'id' => $this->subject->id
        ]);
    }

    public function test_admin_can_store_and_update_subject_with_component_levels()
    {
        $asLevel = \App\Models\Level::where('code', 'AS')->first();
        $aLevel = \App\Models\Level::where('code', 'A')->first();

        // Create AS_A_LEVEL qualification
        $asALevelQual = Qualification::create([
            'qualification_type' => 'AS_A_LEVEL',
            'qualification_name' => 'GCE AS and A Level'
        ]);

        // Store new subject with components tagged with levels
        $response = $this->actingAs($this->admin)->post('/subjects', [
            'qualification_id' => $asALevelQual->id,
            'subject_code' => '9709',
            'subject_name' => 'Mathematics Advanced',
            'components' => [
                [
                    'code' => 'P1',
                    'name' => 'Pure Mathematics 1',
                    'marks' => 75,
                    'level_id' => $asLevel->id
                ],
                [
                    'code' => 'P3',
                    'name' => 'Pure Mathematics 3',
                    'marks' => 75,
                    'level_id' => $aLevel->id
                ]
            ]
        ]);

        $response->assertRedirect('/subjects');
        $this->assertDatabaseHas('subjects', [
            'subject_code' => '9709',
            'qualification_id' => $asALevelQual->id
        ]);

        $subject = Subject::where('subject_code', '9709')->first();
        $this->assertDatabaseHas('components', [
            'subject_id' => $subject->id,
            'component_code' => 'P1',
            'level_id' => $asLevel->id
        ]);
        $this->assertDatabaseHas('components', [
            'subject_id' => $subject->id,
            'component_code' => 'P3',
            'level_id' => $aLevel->id
        ]);

        // Update component levels (change P1 to A Level)
        $comp1 = $subject->components()->where('component_code', 'P1')->first();
        $comp3 = $subject->components()->where('component_code', 'P3')->first();

        $response = $this->actingAs($this->admin)->put("/subjects/{$subject->id}", [
            'subject_code' => '9709',
            'subject_name' => 'Mathematics Advanced',
            'components' => [
                [
                    'id' => $comp1->id,
                    'code' => 'P1',
                    'name' => 'Pure Mathematics 1',
                    'marks' => 75,
                    'level_id' => $aLevel->id // Change to A Level
                ],
                [
                    'id' => $comp3->id,
                    'code' => 'P3',
                    'name' => 'Pure Mathematics 3',
                    'marks' => 75,
                    'level_id' => null // Clear level tag
                ]
            ]
        ]);

        $response->assertRedirect('/subjects');
        $this->assertDatabaseHas('components', [
            'id' => $comp1->id,
            'level_id' => $aLevel->id
        ]);
        $this->assertDatabaseHas('components', [
            'id' => $comp3->id,
            'level_id' => null
        ]);
    }
}
