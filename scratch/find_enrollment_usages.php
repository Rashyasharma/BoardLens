<?php
// Scratch script to find CandidateEnrollment queries and subject_id relations
$dir = new RecursiveDirectoryIterator('C:/Users/HP11/Desktop/My Projects/CambridgeInsights');
$iterator = new RecursiveIteratorIterator($dir);
$matches = [];

foreach ($iterator as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php') {
        continue;
    }
    
    $content = file_get_contents($file->getPathname());
    if (str_contains($content, 'CandidateEnrollment') || str_contains($content, 'candidate_enrollments')) {
        $lines = explode("\n", $content);
        foreach ($lines as $i => $line) {
            if (str_contains($line, 'CandidateEnrollment') || str_contains($line, 'candidate_enrollments') || str_contains($line, 'subject_id')) {
                $matches[] = [
                    'file' => $file->getPathname(),
                    'line' => $i + 1,
                    'content' => trim($line)
                ];
            }
        }
    }
}

foreach ($matches as $match) {
    if (str_contains($match['file'], 'vendor') || str_contains($match['file'], 'storage')) continue;
    echo "File: " . $match['file'] . ":" . $match['line'] . "\n";
    echo "  " . $match['content'] . "\n\n";
}
