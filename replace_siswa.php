<?php
$dir = new RecursiveDirectoryIterator('c:\xampp\htdocs\pmbm');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.(php|html|js|txt)$/i', RecursiveRegexIterator::GET_MATCH);

$c_files = 0;
$c_replacements = 0;

foreach ($files as $file) {
    $path = $file[0];
    if (
        strpos($path, 'vendor') !== false ||
        strpos($path, '.git') !== false ||
        strpos($path, '.agent') !== false ||
        basename($path) == 'test_regex.php' ||
        basename($path) == 'replace_siswa.php' ||
        basename($path) == 'test_output.txt'
    ) {
        continue;
    }

    $content = file_get_contents($path);
    $original_content = $content;

    // Pattern matches exactly 'siswa' or "siswa" (ignoring case) OR standalone Siswa/siswa/SISWA
    $pattern = '/([\'"])(?i:siswa)\1|(?<![\$\_\/\.\-a-zA-Z0-9])(Siswa|siswa|SISWA)(?![\_\/\.\-a-zA-Z0-9])/';

    $new_content = preg_replace_callback($pattern, function ($m) use (&$c_replacements) {
        // If it was captured by group 1, it's wrapped in quotes, leave it alone
        if (!empty($m[1])) {
            return $m[0];
        }

        // Otherwise, it's a standalone word
        $c_replacements++;
        $word = $m[2];
        if ($word === 'Siswa')
            return 'Murid';
        if ($word === 'siswa')
            return 'murid';
        if ($word === 'SISWA')
            return 'MURID';

        return $m[0]; // fallback (shouldn't happen)
    }, $content);

    if ($new_content !== $original_content) {
        file_put_contents($path, $new_content);
        $c_files++;
    }
}

echo "Modified $c_files files with $c_replacements replacements.\n";
?>