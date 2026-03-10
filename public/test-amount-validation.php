<?php
// Test amount validation issues
header('Content-Type: text/plain');

echo "Testing Laravel numeric validation with different amount formats:\n\n";

$testAmounts = [
    '1000.50',    // Standard decimal
    '1000,50',    // European decimal (comma)
    '1,000.50',   // With thousands separator
    '1000',       // Integer
    '0.01',       // Minimum valid
    '0.00',       // Too small
    '-100.50',    // Negative
    'invalid',    // Not a number
    '1000.555',   // Too many decimals
];

foreach ($testAmounts as $amount) {
    echo "Testing amount: '$amount'\n";
    
    // Test is_numeric (what Laravel's 'numeric' rule uses)
    if (is_numeric($amount)) {
        echo "✅ is_numeric() returns true\n";
    } else {
        echo "❌ is_numeric() returns false\n";
    }
    
    // Test float conversion
    $floatVal = floatval($amount);
    echo "  floatval(): $floatVal\n";
    
    // Test for min:0.01
    if ($floatVal >= 0.01) {
        echo "✅ Passes min:0.01 validation\n";
    } else {
        echo "❌ Fails min:0.01 validation\n";
    }
    
    echo "---\n";
}

echo "\nCommon issues with FormData and numeric validation:\n";
echo "1. FormData sends ALL values as strings\n";
echo "2. '1000.50' (string) should pass 'numeric' validation\n";
echo "3. '1,000.50' (with comma) will FAIL 'numeric' validation\n";
echo "4. European format '1000,50' will FAIL 'numeric' validation\n";
echo "5. The 'decimal:2' cast expects a proper decimal value\n";

echo "\nSolution: Ensure JavaScript sends amount as plain number without formatting:\n";
echo "1. Remove thousands separators: '1,000.50' → '1000.50'\n";
echo "2. Use dot as decimal separator: '1000,50' → '1000.50'\n";
echo "3. Parse as float before sending: parseFloat('1000.50')\n";

echo "\nTest current form submission:\n";
if (isset($_POST['amount'])) {
    $amount = $_POST['amount'];
    echo "Amount received: '$amount'\n";
    echo "is_numeric(): " . (is_numeric($amount) ? 'true' : 'false') . "\n";
    echo "floatval(): " . floatval($amount) . "\n";
} else {
    echo "Submit a form with amount field to test\n";
    echo '<form method="POST"><input name="amount"><button>Test</button></form>';
}
?>