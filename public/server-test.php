<?php
header('Content-Type: text/plain');
echo "=== SERVER DIAGNOSTIC ===\n";
echo "Server: " . gethostname() . "\n";
echo "PHP: " . phpversion() . "\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";

$file = '/var/www/app.classapparelph.com/resources/views/inventory/create.blade.php';
echo "\n=== FILE CHECK ===\n";
echo "File exists: " . (file_exists($file) ? 'YES' : 'NO') . "\n";
if (file_exists($file)) {
    echo "File size: " . filesize($file) . " bytes\n";
    echo "File time: " . date('Y-m-d H:i:s', filemtime($file)) . "\n";
    
    // Check for yellow box
    $content = file_get_contents($file);
    if (strpos($content, 'SERVER TEST') !== false) {
        echo "Has YELLOW BOX code: YES\n";
    } else {
        echo "Has YELLOW BOX code: NO\n";
    }
}

echo "\n=== ENV CHECK ===\n";
echo "APP_ENV: " . ($_ENV['APP_ENV'] ?? 'NOT SET') . "\n";
echo "APP_DEBUG: " . ($_ENV['APP_DEBUG'] ?? 'NOT SET') . "\n";
