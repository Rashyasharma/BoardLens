<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiSpreadsheetParser
{
    /**
     * Hardcoded fallback subject mappings
     */
    protected array $fallbackMappings = [
        'BUSINESS STUDIES' => '0450',
        'BUSINESS' => '9609',
        'COMBINED SCIENCE' => '0653',
        'CO-ORD SCIENCES (DOUBLE AWARD)' => '0654',
        'ENGLISH AS A SECOND LANGUAGE' => '0510',
        'HINDI AS A SECOND LANGUAGE' => '0549',
        'INFORMATION AND COMMUNICATION' => '0417',
        'INFORMATION TECHNOLOGY' => '9626',
        'MATHEMATICS' => '0580',
        'MATHEMATICS (W/OUT COURSEWORK)' => '0580',
        'MATHEMATICS ADVANCED' => '9709',
        'PURE MATHEMATICS' => '9709',
        'PHYSICS' => '0625',
        'CHEMISTRY' => '0620',
        'BIOLOGY' => '0610',
        'COMPUTER SCIENCE' => '0478',
        'ECONOMICS' => '0455',
        'ENGLISH GENERAL PAPER' => '8021',
        'PSYCHOLOGY' => '9990',
        'ART AND DESIGN' => '0400',
        'ENVIRONMENTAL MANAGEMENT' => '0680',
        'ACCOUNTING' => '0452',
    ];

    /**
     * Parse the uploaded spreadsheet and return structured candidate and results data.
     */
    public function parse(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File does not exist: {$filePath}");
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($extension === 'pdf') {
            return $this->parsePdf($filePath);
        }

        // Load spreadsheet using PhpSpreadsheet
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) < 2) {
            throw new \Exception("Spreadsheet must contain at least a header row and one data row.");
        }

        $headerRow = $rows[1];
        $sampleRows = array_slice($rows, 1, 5, true);

        // Try AI Mapping first, fall back to local heuristics
        $mapping = $this->getMappingUsingAi($headerRow, $sampleRows);

        $parsedData = [
            'series' => $mapping['series'],
            'qualification' => $mapping['qualification'],
            'ai_used' => $mapping['ai_used'],
            'model_name' => $mapping['model_name'] ?? 'Local Heuristic Fallback',
            'headers' => $headerRow,
            'subjects_mapped' => [],
            'candidates' => [],
        ];

        // Store subject information mapped
        $subjectsMapped = []; // col_key => subject_code
        foreach ($mapping['subjects'] as $col => $code) {
            if ($code && isset($headerRow[$col])) {
                $subjectsMapped[$col] = $code;
                $parsedData['subjects_mapped'][$col] = [
                    'column' => $col,
                    'header_name' => $headerRow[$col],
                    'subject_code' => $code
                ];
            }
        }

        $candNoCol = $mapping['candidate_no_col'] ?? 'A';
        $candNameCol = $mapping['candidate_name_col'] ?? 'B';

        $pumMap = [
            'A*' => 90.0, 'A' => 80.0, 'B' => 70.0, 'C' => 60.0, 'D' => 50.0,
            'E' => 40.0, 'F' => 30.0, 'G' => 20.0, 'U' => 0.0
        ];

        for ($i = 2; $i <= count($rows); $i++) {
            $row = $rows[$i];
            $candNo = isset($row[$candNoCol]) ? trim($row[$candNoCol]) : null;
            $candName = isset($row[$candNameCol]) ? trim($row[$candNameCol]) : null;

            if (empty($candNo) && empty($candName)) {
                continue; // Skip empty rows
            }

            // Pad candidate number to 4 digits if numeric
            if (is_numeric($candNo)) {
                $candNo = str_pad($candNo, 4, '0', STR_PAD_LEFT);
            }

            $candidate = [
                'candidate_number' => $candNo,
                'candidate_name' => $candName,
                'results' => []
            ];

            foreach ($subjectsMapped as $col => $subCode) {
                $rawVal = isset($row[$col]) ? trim($row[$col]) : null;
                if ($rawVal === null || $rawVal === '' || strtolower($rawVal) === 'nan') {
                    continue;
                }

                // Parse grade and PUM from cell
                $grade = $rawVal;
                $pum = null;

                // Handle format like "A(82)" or "A* (91)"
                if (preg_match('/^([A-Za-z*]+)\s*\((\d+)\)$/', $rawVal, $matches)) {
                    $grade = $matches[1];
                    $pum = (float)$matches[2];
                } 
                // Handle format where PUM is just a number (e.g. 82)
                elseif (is_numeric($rawVal)) {
                    $pum = (float)$rawVal;
                    // Back-calculate grade if not present based on PUM
                    if ($pum >= 90) $grade = 'A*';
                    elseif ($pum >= 80) $grade = 'A';
                    elseif ($pum >= 70) $grade = 'B';
                    elseif ($pum >= 60) $grade = 'C';
                    elseif ($pum >= 50) $grade = 'D';
                    elseif ($pum >= 40) $grade = 'E';
                    else $grade = 'U';
                }

                if (strcasecmp($grade, 'ungraded') === 0) {
                    $grade = 'U';
                }

                $gradeUpper = strtoupper(trim($grade));
                if (in_array($gradeUpper, ['PENDING', 'PEND', 'Q'])) {
                    $grade = 'Q';
                    $pum = 0.0;
                } elseif (in_array($gradeUpper, ['NO RESULT', 'NORESULT', 'NO_RESULT', 'X'])) {
                    $grade = 'X';
                    $pum = 0.0;
                }

                // If PUM is still null, map from standard grade fallback using midpoint ranges
                if ($pum === null) {
                    $pum = $this->getMidpointPum($grade, $parsedData['qualification'], $subCode);
                }

                $candidate['results'][$subCode] = [
                    'grade' => $grade,
                    'pum' => $pum,
                    'raw_value' => $rawVal
                ];
            }

            $parsedData['candidates'][] = $candidate;
        }

        return $parsedData;
    }

    /**
     * Attempt to query Gemini API to get structural mappings, with local fallback.
     */
    protected function getMappingUsingAi(array $headers, array $sampleRows): array
    {
        $apiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY');

        if (!$apiKey) {
            Log::info("Gemini API Key missing. Falling back to local heuristic mapper.");
            return $this->getLocalHeuristicMapping($headers, $sampleRows);
        }

        try {
            $prompt = $this->buildPrompt($headers, $sampleRows);
            
            // Call Gemini API (using gemini-1.5-flash model)
            $response = Http::withHeaders([
                'Content-Type' => 'application/json'
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json'
                ]
            ]);

            if ($response->successful()) {
                $resultJson = $response->json();
                $responseText = $resultJson['candidates'][0]['content']['parts'][0]['text'] ?? null;
                
                if ($responseText) {
                    $data = json_decode($responseText, true);
                    if ($data && isset($data['subjects']) && isset($data['series'])) {
                        $data['ai_used'] = true;
                        $data['model_name'] = 'Gemini 1.5 Flash';
                        return $data;
                    }
                }
            }
            
            Log::warning("Gemini API request failed or returned invalid JSON. Response: " . $response->body());
        } catch (\Exception $e) {
            Log::error("Error calling Gemini API: " . $e->getMessage());
        }

        return $this->getLocalHeuristicMapping($headers, $sampleRows);
    }

    /**
     * Build system prompt for Gemini mapping.
     */
    protected function buildPrompt(array $headers, array $sampleRows): string
    {
        $headersStr = json_encode($headers, JSON_PRETTY_PRINT);
        $sampleStr = json_encode($sampleRows, JSON_PRETTY_PRINT);

        return <<<PROMPT
You are a Cambridge Exam Portal AI assistant.
Your task is to analyze the header columns and sample rows from an uploaded broadsheet spreadsheet and return a mapping JSON.

Headers:
{$headersStr}

Sample Rows:
{$sampleStr}

Please identify:
1. The exam series month (must be "March", "June", or "November") and year (4 digits, e.g. 2025 or 2026).
2. The qualification type (either "IGCSE" or "AS_A_LEVEL"). If not obvious, default to "IGCSE".
3. Which column letter contains the candidate number (typically "Cand. No", "Cand No", "Candidate No", "No", etc.).
4. Which column letter contains the candidate name (typically "Candidate Name", "Name", etc.).
5. Map column letters of all subject/syllabus columns to their standard 4-digit Cambridge subject code.
   - Mathematics -> 0580 (IGCSE) or 9709 (AS_A_LEVEL)
   - Business Studies -> 0450 (IGCSE) or 9609 (AS_A_LEVEL)
   - Combined Science -> 0653
   - Co-ordinated Sciences (Double Award) -> 0654
   - English as a Second Language -> 0510
   - Hindi as a Second Language -> 0549
   - Information and Communication -> 0417
   - Information Technology -> 9626
   - Chemistry -> 0620 (IGCSE) or 9701 (AS_A_LEVEL)
   - Physics -> 0625 (IGCSE) or 9702 (AS_A_LEVEL)
   - Biology -> 0610 (IGCSE) or 9700 (AS_A_LEVEL)
   - Accounting -> 0452 (IGCSE) or 9706 (AS_A_LEVEL)
   - Economics -> 0455 (IGCSE) or 9708 (AS_A_LEVEL)

Return ONLY a valid JSON object matching this schema:
{
  "series": {
    "month": "March" | "June" | "November",
    "year": integer
  },
  "qualification": "IGCSE" | "AS_A_LEVEL",
  "candidate_no_col": "column_letter (e.g. A)",
  "candidate_name_col": "column_letter (e.g. B)",
  "subjects": {
    "column_letter (e.g. C)": "4_digit_subject_code",
    "column_letter (e.g. D)": "4_digit_subject_code"
  }
}
PROMPT;
    }

    /**
     * Local heuristic mappings fallback when API key is missing or calls fail.
     */
    protected function getLocalHeuristicMapping(array $headers, array $sampleRows): array
    {
        $mapping = [
            'series' => [
                'month' => 'March',
                'year' => 2026
            ],
            'qualification' => 'IGCSE',
            'candidate_no_col' => 'A',
            'candidate_name_col' => 'B',
            'subjects' => [],
            'ai_used' => false,
            'model_name' => 'Local Heuristic Fallback'
        ];

        // Attempt to find month and year from sample rows or keys
        // If not found, default to March 2026 (or extract from first header)
        foreach ($headers as $col => $header) {
            if (!$header) continue;
            $headerUpper = strtoupper(trim($header));

            if (in_array($headerUpper, ['CAND. NO', 'CAND NO', 'CANDIDATE NO', 'CANDIDATE NUMBER', 'NO.'])) {
                $mapping['candidate_no_col'] = $col;
            } elseif (in_array($headerUpper, ['CANDIDATE NAME', 'NAME', 'CANDIDATE'])) {
                $mapping['candidate_name_col'] = $col;
            } else {
                // Check if it matches any fallback subject mappings
                $found = false;
                foreach ($this->fallbackMappings as $name => $code) {
                    if (str_contains($headerUpper, $name)) {
                        $mapping['subjects'][$col] = $code;
                        $found = true;
                        
                        // Infer qualification if a GCE code is matched
                        if (in_array($code, ['9709', '9609', '9626', '9701', '9702', '9700', '9706', '9708', '8021', '9990'])) {
                            $mapping['qualification'] = 'AS_A_LEVEL';
                        }
                        break;
                    }
                }
            }
        }

        return $mapping;
    }

    protected function parsePdf(string $filePath): array
    {
        $scriptPath = app_path('Services/pdf_parser.py');
        $command = 'python ' . escapeshellarg($scriptPath) . ' ' . escapeshellarg($filePath);
        $output = shell_exec($command);
        
        if (empty($output)) {
            throw new \Exception("PDF parsing failed. Python script returned empty output.");
        }

        $decoded = json_decode($output, true);
        if (!$decoded || isset($decoded['error'])) {
            throw new \Exception("PDF parsing error: " . ($decoded['error'] ?? 'Invalid JSON returned from Python script.'));
        }

        // Trigger AI structured validation fallback if heuristic results are low confidence
        $candidateCount = count($decoded['candidates'] ?? []);
        if ($candidateCount === 0 && !empty($decoded['raw_text'])) {
            Log::info("Rule-based parser returned 0 candidates. Falling back to Gemini API structured parser.");
            $decoded = $this->parsePdfTextUsingAi($decoded['raw_text'], $decoded);
        }

        // AI Verification Audit if API key is active
        $apiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY');
        if ($apiKey && !empty($decoded['candidates']) && !empty($decoded['raw_text'])) {
            try {
                $decoded = $this->auditParsedDataWithAi($decoded, $apiKey);
            } catch (\Exception $e) {
                Log::warning("Gemini AI Auditor warning: " . $e->getMessage());
            }
        }

        return $decoded;
    }

    /**
     * Fallback structured parser using Gemini API
     */
    protected function parsePdfTextUsingAi(string $rawText, array $fallbackData): array
    {
        $apiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY');
        if (!$apiKey) {
            return $fallbackData;
        }

        try {
            // Keep prompt small by taking first 15000 chars of raw text to avoid rate limits
            $textSample = substr($rawText, 0, 30000);
            $prompt = <<<PROMPT
You are a Cambridge Exam Portal AI assistant.
Analyze this raw text extracted from a Cambridge Statements of Results PDF document and convert it into a structured JSON.

Raw Text Content:
{$textSample}

Return a valid JSON object matching this exact schema:
{
  "series": {
    "month": "March" | "June" | "November",
    "year": integer
  },
  "qualification": "IGCSE" | "AS_A_LEVEL",
  "subjects_mapped": {
    "subject_code (4 digits, e.g. 0580)": {
      "column": "subject_code",
      "header_name": "SUBJECT NAME",
      "subject_code": "subject_code",
      "qualification": "IGCSE" | "AS_A_LEVEL"
    }
  },
  "candidates": [
    {
      "candidate_number": "4 digits candidate number (padded e.g. 0023)",
      "candidate_name": "CANDIDATE FULL NAME",
      "results": {
        "subject_code": {
          "grade": "grade string (e.g. A*, A, B, U)",
          "pum": float_percentage_uniform_mark,
          "raw_value": "raw grade and pum representation"
        }
      }
    }
  ]
}

Only return raw JSON string, do not wrap it in markdown blocks.
PROMPT;

            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [['parts' => [['text' => $prompt]]]]
                ]);

            if ($response->successful()) {
                $res = $response->json();
                $txt = $res['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $cleanTxt = trim(preg_replace('/^```json|```$/', '', $txt));
                $parsedAi = json_decode($cleanTxt, true);
                if ($parsedAi && isset($parsedAi['candidates'])) {
                    $parsedAi['ai_used'] = true;
                    $parsedAi['model_name'] = 'Gemini 1.5 Flash (Fallback Parser)';
                    $parsedAi['raw_text'] = $rawText;
                    return $parsedAi;
                }
            }
        } catch (\Exception $e) {
            Log::error("Gemini Fallback Parser failed: " . $e->getMessage());
        }

        return $fallbackData;
    }

    /**
     * Audit and verify the parsed results with Gemini API to ensure no missing grades/candidates.
     */
    protected function auditParsedDataWithAi(array $parsedData, string $apiKey): array
    {
        $candidatesAuditList = array_map(function ($c) {
            return [
                'candidate_number' => $c['candidate_number'],
                'name' => $c['candidate_name'],
                'subjects' => array_keys($c['results'] ?? [])
            ];
        }, $parsedData['candidates']);

        $sampleText = substr($parsedData['raw_text'], 0, 20000);
        $candidatesAuditJson = json_encode($candidatesAuditList);

        $prompt = <<<PROMPT
Compare the following parsed candidates summary list with the raw PDF text snippet to audit for any missing candidates, wrong subject lists, or parsing errors.

Parsed Summary List:
{$candidatesAuditJson}

Raw PDF Text Snippet:
{$sampleText}

Return a valid JSON string indicating if there is any mismatch:
{
  "mismatch_found": boolean,
  "mismatch_details": "string description of mismatches, or empty if none",
  "missing_candidate_numbers": ["4-digit numbers if any are entirely missing"]
}
PROMPT;

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                'contents' => [['parts' => [['text' => $prompt]]]]
            ]);

        if ($response->successful()) {
            $res = $response->json();
            $txt = $res['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $cleanTxt = trim(preg_replace('/^```json|```$/', '', $txt));
            $auditResult = json_decode($cleanTxt, true);
            if ($auditResult) {
                $parsedData['ai_audit'] = $auditResult;
            }
        }

        return $parsedData;
    }

    public function getMidpointPum(string $grade, ?string $qualification = null, ?string $subjectCode = null): float
    {
        $g = trim($grade);
        
        if (in_array($g, ['a', 'b', 'c', 'd', 'e'])) {
            $asMap = [
                'a' => 90.0, // Midpoint of 80-100 range
                'b' => 74.5, // Midpoint of 70-79 range
                'c' => 64.5, // Midpoint of 60-69 range
                'd' => 54.5, // Midpoint of 50-59 range
                'e' => 44.5  // Midpoint of 40-49 range
            ];
            return $asMap[$g] ?? 0.0;
        }

        $gUpper = strtoupper($g);
        switch ($gUpper) {
            case 'A*':
            case 'A*A*':
                return 95.0; // Midpoint of 90-100
            case 'A':
            case 'AA':
                return 84.5; // Midpoint of 80-89
            case 'B':
            case 'BB':
                return 74.5; // Midpoint of 70-79
            case 'C':
            case 'CC':
                return 64.5; // Midpoint of 60-69
            case 'D':
            case 'DD':
                return 54.5; // Midpoint of 50-59
            case 'E':
            case 'EE':
                return 44.5; // Midpoint of 40-49
            case 'F':
            case 'FF':
                return 34.5; // Midpoint of 30-39
            case 'G':
            case 'GG':
                return 24.5; // Midpoint of 20-29
            default:
                return 0.0;
        }
    }
}
