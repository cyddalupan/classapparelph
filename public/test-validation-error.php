<?php
// Simple test to see what validation error we're getting
header('Content-Type: text/plain');

// Test different date formats
$testDates = [
    '2026-02-28',  // HTML date input format
    '28/02/2026',  // European format
    '02/28/2026',  // US format
    '2026-02-28 00:00:00', // DateTime format
];

echo "Testing Laravel date validation patterns:\n\n";

foreach ($testDates as $date) {
    echo "Testing: '$date'\n";
    
    // Simulate Laravel validation
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        echo "✅ Matches YYYY-MM-DD pattern\n";
    } else {
        echo "❌ Does NOT match YYYY-MM-DD pattern\n";
    }
    
    // Check if it's a valid date
    $dateTime = DateTime::createFromFormat('Y-m-d', $date);
    if ($dateTime && $dateTime->format('Y-m-d') === $date) {
        echo "✅ Valid date for 'Y-m-d' format\n";
    } else {
        echo "❌ Invalid date for 'Y-m-d' format\n";
    }
    
    echo "---\n";
}

echo "\nCommon Laravel validation patterns that cause 'string did not match expected pattern':\n";
echo "1. 'date' rule expects Y-m-d format\n";
echo "2. 'date_format:Y-m-d' is more strict\n";
echo "3. 'regex:/^\d{4}-\d{2}-\d{2}$/' would be explicit\n";
echo "4. 'before:today' or 'after:2024-01-01' also validate dates\n";

echo "\nTest HTML date input value:\n";
if (isset($_GET['date'])) {
    $htmlDate = $_GET['date'];
    echo "HTML date input sent: '$htmlDate'\n";
    
    // Check what Laravel sees
    $dateTime = DateTime::createFromFormat('Y-m-d', $htmlDate);
    if ($dateTime && $dateTime->format('Y-m-d') === $htmlDate) {
        echo "✅ Laravel should accept this as valid date\n";
    } else {
        echo "❌ Laravel will reject this date\n";
    }
} else {
    echo "Add ?date=2026-02-28 to test a specific date\n";
}

echo "\nTo debug the actual error, check:\n";
echo "1. Browser console (F12) for JavaScript errors\n";
echo "2. Laravel logs: /var/www/app.classapparelph.com/storage/logs/laravel.log\n";
echo "3. Network tab in browser to see the exact request being sent\n";
?>