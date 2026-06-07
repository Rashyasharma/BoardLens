<?php
$dir = new RecursiveDirectoryIterator('C:/Users/HP11/Desktop/My Projects/CambridgeInsights');
$iterator = new RecursiveIteratorIterator($dir);
$matches = [];

foreach ($iterator as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php' && $file->getExtension() !== 'js') {
        continue;
    }
    $content = file_get_contents($file->getPathname());
    if (str_contains($content, 'api/subjects')) {
        $lines = explode("\n", $content);
        foreach ($lines as $i => $line) {
            $matches[] = [
                'file' => $file->getPathname(),
                'line' => $i + 1,
                'content' => trim($line)
            ];
        }
    }
}

foreach ($matches as $m) {
    if (str_contains($m['file'], 'vendor') || str_contains($m['file'], 'storage')) continue;
    echo $m['file'] . ":" . $m['line'] . "\n" . $m['content'] . "\n\n";
}
