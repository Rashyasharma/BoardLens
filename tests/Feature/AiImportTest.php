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
use Illuminate\Support\Facades\Session;

class AiImportTest extends TestCase
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
            'school_name' => 'Lucky International School',
            'school_code' => 'LKY001'
        ]);

        $this->examOfficer = User::create([
            'name' => 'Exam Officer',
            'username' => 'officer_test',
            'email' => 'officer_test@cep.local',
            'password' => 'password',
            'role' => 'exam_officer',
            'school_id' => $this->school->id
        ]);

        $igcse = Qualification::create([
            'qualification_type' => 'IGCSE',
            'qualification_name' => 'IGCSE'
        ]);

        Qualification::create([
            'qualification_type' => 'AS_A_LEVEL',
            'qualification_name' => 'GCE AS and A Level'
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
            'qualification_id' => $igcse->id,
            'total_marks' => 200,
            'passing_percentage' => 40.00
        ]);

        Subject::create([
            'subject_code' => '8021',
            'subject_name' => 'English General Paper',
            'qualification_id' => $igcse->id,
            'total_marks' => 100,
            'passing_percentage' => 40.00
        ]);
    }

    /**
     * Test uploader page loads successfully.
     */
    public function test_importer_page_is_accessible()
    {
        $response = $this->actingAs($this->examOfficer)
            ->get('/uploads/ai-importer');

        $response->assertStatus(200);
        $response->assertSee('Upload Statements of Results');
        $response->assertSee('Lucky International School');
    }

    /**
     * Test uploading a spreadsheet generates a preview.
     */
    public function test_uploading_spreadsheet_creates_preview()
    {
        // CSV with headers containing Candidate No, Candidate Name, and a subject column mapped to 0580
        $csvContent = "Candidate Number,Student Name,MATHEMATICS\n" .
                      "0001,John Doe,A(85)\n" .
                      "0002,Jane Smith,B(72)\n";

        $file = UploadedFile::fake()->createWithContent('broadsheet.csv', $csvContent);

        $response = $this->actingAs($this->examOfficer)
            ->post('/uploads/ai-importer/preview', [
                'statement_files' => [$file]
            ]);

        $response->assertStatus(200);
        $response->assertSee('Verify Extracted Data');
        $response->assertSee('John Doe');
        $response->assertSee('Jane Smith');
        
        // Assert session has the data stored
        $sessionKey = $response->viewData('sessionKey');
        $this->assertNotNull($sessionKey);
        $this->assertTrue(Session::has($sessionKey));
    }

    /**
     * Test uploading a PDF generates a preview.
     */
    public function test_uploading_pdf_creates_preview()
    {
        $pdfPath = 'C:\Users\HP11\Desktop\Electronic Statements of Results for March 2026.pdf';
        
        if (!file_exists($pdfPath)) {
            $this->markTestSkipped('Real PDF file not found on Desktop.');
            return;
        }

        // Create an UploadedFile instance referencing the real PDF file
        $file = new UploadedFile(
            $pdfPath,
            'Electronic Statements of Results for March 2026.pdf',
            'application/pdf',
            null,
            true // Mark as test mode
        );

        $response = $this->actingAs($this->examOfficer)
            ->post('/uploads/ai-importer/preview', [
                'statement_files' => [$file]
            ]);

        $response->assertStatus(200);
        $response->assertSee('Verify Extracted Data');
        $response->assertSee('NIKHIL KACHHWAHA');
        $response->assertSee('AYUSHMAN SINGH RAJPUROHIT');
        
        // Assert session has the data stored
        $sessionKey = $response->viewData('sessionKey');
        $this->assertNotNull($sessionKey);
        $this->assertTrue(Session::has($sessionKey));
    }

    /**
     * Test confirming the import writes data to the database.
     */
    public function test_confirming_import_persists_to_database()
    {
        // Pre-simulate session data
        $sessionKey = 'ai_import_test_key';
        
        Session::put($sessionKey, [
            'school_id' => $this->school->id,
            'files_data' => [
                [
                    'file_name' => 'broadsheet.csv',
                    'series_id' => $this->series->id,
                    'series_name' => 'March 2026',
                    'qualification_id' => $this->subject->qualification_id,
                    'qualification_name' => 'IGCSE',
                    'model_name' => 'Local Heuristic Fallback',
                    'ai_used' => false,
                    'subjects' => [
                        'C' => [
                            'column' => 'C',
                            'header_name' => 'MATHEMATICS',
                            'subject_code' => '0580'
                        ]
                    ],
                    'comparison' => [
                        'stats' => [
                            'total_parsed' => 1,
                            'new_candidates' => 1,
                            'new_results' => 1,
                            'updated_results' => 0,
                            'no_change_results' => 0
                        ],
                        'candidates' => [
                            [
                                'candidate_number' => '0001',
                                'candidate_name' => 'John Doe',
                                'status' => 'new',
                                'results' => [
                                    '0580' => [
                                        'grade' => 'A',
                                        'pum' => 85,
                                        'raw_value' => 'A(85)',
                                        'status' => 'new'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $response = $this->actingAs($this->examOfficer)
            ->post('/uploads/ai-importer/confirm', [
                'session_key' => $sessionKey
            ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        // Verify Candidate was created
        $this->assertDatabaseHas('candidates', [
            'candidate_number' => '0001',
            'candidate_name' => 'John Doe',
            'school_id' => $this->school->id
        ]);

        // Verify SubjectResult was created
        $this->assertDatabaseHas('subject_results', [
            'grade' => 'A',
            'pum' => 85,
            'series_id' => $this->series->id,
            'subject_id' => $this->subject->id
        ]);
    }

    /**
     * Test uploading a spreadsheet with "Pending" results maps them to Q and PUM 0.
     */
    public function test_pending_grades_map_to_q_and_zero_pum()
    {
        $csvContent = "Candidate Number,Student Name,MATHEMATICS\n" .
                      "0003,Alice Pending,Pending\n" .
                      "0004,Bob NoResult,No Result\n" .
                      "0005,Charlie Absent,X\n";

        $file = UploadedFile::fake()->createWithContent('broadsheet_pending.csv', $csvContent);

        $response = $this->actingAs($this->examOfficer)
            ->post('/uploads/ai-importer/preview', [
                'statement_files' => [$file]
            ]);

        $response->assertStatus(200);
        
        $sessionKey = $response->viewData('sessionKey');
        $this->assertNotNull($sessionKey);
        
        $sessionData = Session::get($sessionKey);
        $candidates = $sessionData['files_data'][0]['comparison']['candidates'];

        // Assert Alice Pending is mapped to Q and 0 PUM
        $alice = collect($candidates)->firstWhere('candidate_name', 'Alice Pending');
        $this->assertNotNull($alice);
        $this->assertEquals('Q', $alice['results']['0580']['grade']);
        $this->assertEquals(0.0, $alice['results']['0580']['pum']);

        // Assert Bob NoResult is mapped to X and 0 PUM
        $bob = collect($candidates)->firstWhere('candidate_name', 'Bob NoResult');
        $this->assertNotNull($bob);
        $this->assertEquals('X', $bob['results']['0580']['grade']);
        $this->assertEquals(0.0, $bob['results']['0580']['pum']);

        // Assert Charlie Absent is mapped to X and 0 PUM
        $charlie = collect($candidates)->firstWhere('candidate_name', 'Charlie Absent');
        $this->assertNotNull($charlie);
        $this->assertEquals('X', $charlie['results']['0580']['grade']);
        $this->assertEquals(0.0, $charlie['results']['0580']['pum']);
    }

    /**
     * Test uploading results without PUM fallback to correct midpoint ranges.
     */
    public function test_midpoint_pum_mapping_for_grades()
    {
        $csvContent = "Candidate Number,Student Name,MATHEMATICS,ENGLISH GENERAL PAPER\n" .
                      "0003,Alice Star,A*,a\n" .
                      "0004,Bob DoubleStar,A*A*,e\n" .
                      "0005,Charlie A,A,c\n";

        $file = UploadedFile::fake()->createWithContent('broadsheet_midpoints.csv', $csvContent);

        $response = $this->actingAs($this->examOfficer)
            ->post('/uploads/ai-importer/preview', [
                'statement_files' => [$file]
            ]);

        $response->assertStatus(200);
        
        $sessionKey = $response->viewData('sessionKey');
        $this->assertNotNull($sessionKey);
        
        $sessionData = Session::get($sessionKey);
        $candidates = $sessionData['files_data'][0]['comparison']['candidates'];

        // Assert A* maps to 95.0 PUM
        $alice = collect($candidates)->firstWhere('candidate_name', 'Alice Star');
        $this->assertNotNull($alice);
        $this->assertEquals(95.0, $alice['results']['0580']['pum']);
        // Assert AS Level 'a' maps to 90.0 PUM
        $this->assertEquals(90.0, $alice['results']['8021']['pum']);

        // Assert A*A* maps to 95.0 PUM
        $bob = collect($candidates)->firstWhere('candidate_name', 'Bob DoubleStar');
        $this->assertNotNull($bob);
        $this->assertEquals(95.0, $bob['results']['0580']['pum']);
        // Assert AS Level 'e' maps to 45.0 PUM
        $this->assertEquals(45.0, $bob['results']['8021']['pum']);

        // Assert A maps to 85.0 PUM
        $charlie = collect($candidates)->firstWhere('candidate_name', 'Charlie A');
        $this->assertNotNull($charlie);
        $this->assertEquals(85.0, $charlie['results']['0580']['pum']);
        // Assert AS Level 'c' maps to 65.0 PUM
        $this->assertEquals(65.0, $charlie['results']['8021']['pum']);
    }
}
