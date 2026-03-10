<?php
// Test decimal casting issue
require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

// Test the decimal cast
echo "<h1>Testing Decimal Cast</h1>\n";

$testValues = [
    '1000.50',    // Good
    '1,000.50',   // With comma
    '1000,50',    // European
    '1000',       // Integer
    '0.01',       // Small
    'invalid',    // Not numeric
    '1000.555',   // More than 2 decimals
];

foreach ($testValues as $value) {
    echo "<h3>Testing: '$value'</h3>\n";
    
    // Test is_numeric first
    echo "is_numeric(): " . (is_numeric($value) ? 'true' : 'false') . "<br>\n";
    
    // Test floatval
    $float = floatval($value);
    echo "floatval(): $float<br>\n";
    
    // Test what Laravel's decimal cast might do
    try {
        // Simulate Laravel's decimal cast
        if (!is_numeric($value)) {
            throw new InvalidArgumentException("Value must be numeric");
        }
        
        $decimal = number_format((float) $value, 2, '.', '');
        echo "Decimal cast (simulated): $decimal<br>\n";
        echo "<span style='color: green;'>✅ Would pass decimal:2 cast</span><br>\n";
    } catch (Exception $e) {
        echo "<span style='color: red;'>❌ Would fail: " . $e->getMessage() . "</span><br>\n";
    }
    
    echo "<hr>\n";
}

// Test actual Laravel model behavior
echo "<h2>Testing with actual Expense model</h2>\n";

try {
    // Create a test expense instance
    $expense = new \App\Models\Expense();
    
    // Test setting different values
    $testCases = [
        ['date' => '2026-02-28', 'amount' => '1000.50'],
        ['date' => '2026-02-28', 'amount' => '1,000.50'],
        ['date' => '2026-02-28', 'amount' => 'invalid'],
    ];
    
    foreach ($testCases as $i => $case) {
        echo "<h3>Test Case $i: amount = '{$case['amount']}'</h3>\n";
        
        try {
            $expense->fill($case);
            echo "Model fill succeeded<br>\n";
            echo "Date attribute: " . $expense->date . "<br>\n";
            echo "Amount attribute: " . $expense->amount . " (type: " . gettype($expense->amount) . ")<br>\n";
            
            // Try to save (will fail without DB connection, but we can see casting)
            echo "<span style='color: green;'>✅ Casting worked</span><br>\n";
        } catch (Exception $e) {
            echo "<span style='color: red;'>❌ Error: " . $e->getMessage() . "</span><br>\n";
        }
        
        echo "<hr>\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>\n";
}

// Show current PHP and Laravel info
echo "<h2>Environment Info</h2>\n";
echo "PHP Version: " . PHP_VERSION . "<br>\n";
echo "Laravel Version: " . app()->version() . "<br>\n";

// Check if we can connect to database
try {
    \DB::connection()->getPdo();
    echo "Database: Connected<br>\n";
} catch (Exception $e) {
    echo "Database: Not connected - " . $e->getMessage() . "<br>\n";
}
?>