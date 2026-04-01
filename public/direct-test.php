<?php
// DIRECT TEST - Bypass Laravel completely
echo "<h1>DIRECT PHP TEST</h1>";
echo "<p>Server: " . gethostname() . "</p>";
echo "<p>Time: " . date('Y-m-d H:i:s') . "</p>";

// Try to include the Blade file directly (won't work fully but will show errors)
$bladeFile = '/var/www/app.classapparelph.com/resources/views/inventory/create.blade.php';
echo "<p>Blade file exists: " . (file_exists($bladeFile) ? 'YES' : 'NO') . "</p>";
echo "<p>Blade file size: " . filesize($bladeFile) . " bytes</p>";

// Try to read first few lines
$lines = file($bladeFile, FILE_IGNORE_NEW_LINES);
echo "<p>First 3 lines of file:</p>";
echo "<pre>";
for ($i = 0; $i < 3 && $i < count($lines); $i++) {
    echo htmlspecialchars($lines[$i]) . "\n";
}
echo "</pre>";

// Check for our test-div
$content = file_get_contents($bladeFile);
if (strpos($content, 'test-div') !== false) {
    echo "<p style='color: green; font-weight: bold;'>✅ test-div FOUND in file!</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ test-div NOT FOUND in file!</p>";
}

// Check last modification time
echo "<p>File last modified: " . date('Y-m-d H:i:s', filemtime($bladeFile)) . "</p>";
