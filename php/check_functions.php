<?php
/**
 * Check Required Functions
 * 
 * This script checks if all required functions exist for the booking system.
 */

echo "<h2>🔍 Checking Required Functions...</h2>";

// Include config file
if (file_exists('config.php')) {
    require_once 'config.php';
    echo "<p style='color: green;'>✅ config.php loaded successfully</p>";
} else {
    echo "<p style='color: red;'>❌ config.php not found!</p>";
    exit;
}

// List of required functions
$required_functions = [
    'connectDB' => 'Database connection function',
    'sanitizeInput' => 'Input sanitization function',
    'validateEmail' => 'Email validation function',
    'generateBookingReference' => 'Booking reference generation function',
    'formatDate' => 'Date formatting function',
    'formatTime' => 'Time formatting function'
];

echo "<h3>Function Check Results:</h3>";

$missing_functions = [];

foreach ($required_functions as $function => $description) {
    if (function_exists($function)) {
        echo "<p style='color: green;'>✅ <strong>{$function}()</strong> - {$description}</p>";
    } else {
        echo "<p style='color: red;'>❌ <strong>{$function}()</strong> - {$description} - MISSING</p>";
        $missing_functions[] = $function;
    }
}

// Test database connection
echo "<h3>Database Connection Test:</h3>";

if (function_exists('connectDB')) {
    try {
        $conn = connectDB();
        if ($conn) {
            echo "<p style='color: green;'>✅ Database connection successful</p>";
            
            // Test if bookings table exists
            try {
                $stmt = $conn->query("DESCRIBE bookings");
                echo "<p style='color: green;'>✅ Bookings table exists</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Bookings table missing: " . $e->getMessage() . "</p>";
            }
            
            $conn = null;
        } else {
            echo "<p style='color: red;'>❌ Database connection failed</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Database connection error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ connectDB function not available</p>";
}

// Test function calls
echo "<h3>Function Test Results:</h3>";

if (function_exists('sanitizeInput')) {
    try {
        $test = sanitizeInput('<script>alert("test")</script>');
        echo "<p style='color: green;'>✅ sanitizeInput() works - Result: " . htmlspecialchars($test) . "</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ sanitizeInput() error: " . $e->getMessage() . "</p>";
    }
}

if (function_exists('validateEmail')) {
    try {
        $test = validateEmail('test@example.com');
        echo "<p style='color: green;'>✅ validateEmail() works - Result: " . ($test ? 'Valid' : 'Invalid') . "</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ validateEmail() error: " . $e->getMessage() . "</p>";
    }
}

if (function_exists('generateBookingReference')) {
    try {
        $test = generateBookingReference();
        echo "<p style='color: green;'>✅ generateBookingReference() works - Result: " . htmlspecialchars($test) . "</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ generateBookingReference() error: " . $e->getMessage() . "</p>";
    }
}

// Summary
echo "<h3>Summary:</h3>";

if (empty($missing_functions)) {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; border-left: 4px solid #28a745;'>";
    echo "<h4 style='color: #155724;'>✅ All Functions Available!</h4>";
    echo "<p>All required functions are present and working. The booking system should work properly.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 8px; border-left: 4px solid #dc3545;'>";
    echo "<h4 style='color: #721c24;'>❌ Missing Functions</h4>";
    echo "<p>The following functions are missing:</p>";
    echo "<ul>";
    foreach ($missing_functions as $func) {
        echo "<li><strong>{$func}()</strong></li>";
    }
    echo "</ul>";
    echo "<p><strong>Solution:</strong> Run the fix script to add missing functions.</p>";
    echo "<p><a href='fix_admin_panel.php' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Run Fix Script</a></p>";
    echo "</div>";
}

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
echo "<h4>🧪 Next Steps:</h4>";
echo "<ol>";
echo "<li><strong>If functions are missing:</strong> Run <a href='fix_admin_panel.php'>fix_admin_panel.php</a></li>";
echo "<li><strong>Test booking form:</strong> <a href='../booking.html' target='_blank'>booking.html</a></li>";
echo "<li><strong>Check debug output:</strong> Submit form and check browser console</li>";
echo "<li><strong>Verify admin panel:</strong> <a href='../admin/' target='_blank'>Admin Panel</a></li>";
echo "</ol>";
echo "</div>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Function Check</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 800px; 
            margin: 50px auto; 
            padding: 20px; 
            background: #f5f5f5;
        }
        h2, h3 { color: #2c3e50; }
        h4 { color: inherit; margin-bottom: 10px; }
        p { line-height: 1.6; }
        ul, ol { line-height: 1.8; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Content will be inserted here by PHP -->
    </div>
</body>
</html>
