<?php
$dir = new RecursiveDirectoryIterator('c:\xampp\htdocs\pmbm');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.(php|html|txt)$/i', RecursiveRegexIterator::GET_MATCH);

$c = 0;
foreach ($files as $file) {
    if (strpos($file[0], 'vendor') !== false || strpos($file[0], '.git') !== false || strpos($file[0], '.agent') !== false || basename($file[0]) == 'test_regex.php' || basename($file[0]) == 'test_output.txt')
        continue;

    $content = file_get_contents($file[0]);
    $lines = explode("\n", $content);
    foreach ($lines as $ln => $line) {
        if (preg_match('/(?<![\$\_\/\.\-a-zA-Z0-9])(Siswa|siswa|SISWA)(?![\_\/\.\-a-zA-Z0-9])/', $line)) {
            echo basename($file[0]) . ":" . ($ln + 1) . " -> " . trim($line) . "\n";
            $c++;
        }
    }
}
echo "Total matches: $c\n";
?>