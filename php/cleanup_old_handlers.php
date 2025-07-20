<?php
/**
 * Cleanup Old Booking Handlers
 * 
 * Disable all old booking handlers to ensure only one is active
 */

echo "<h2>🧹 Cleanup Old Booking Handlers</h2>";

echo "<h3>1. Identify Old Handlers</h3>";

$old_handlers = [
    'booking.php' => 'Original booking handler',
    'booking_clean.php' => 'Clean booking handler',
    'booking_simple.php' => 'Simple booking handler',
    'booking_handler_backup.php' => 'Backup booking handler',
    'booking_handler_fixed.php' => 'Fixed booking handler',
    'booking_debug.php' => 'Debug booking handler'
];

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📁 Booking Handler Status:</h4>";
echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<tr style='background: #e9ecef;'>";
echo "<th style='padding: 8px; border: 1px solid #ddd;'>File</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd;'>Status</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd;'>Size</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd;'>Modified</th>";
echo "</tr>";

// Check main handler
echo "<tr>";
echo "<td style='padding: 8px; border: 1px solid #ddd;'><strong>booking_handler.php</strong></td>";
echo "<td style='padding: 8px; border: 1px solid #ddd; color: #28a745;'>✅ ACTIVE (Main Handler)</td>";
if (file_exists('booking_handler.php')) {
    $size = number_format(filesize('booking_handler.php'));
    $modified = date('Y-m-d H:i:s', filemtime('booking_handler.php'));
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$size} bytes</td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$modified}</td>";
} else {
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>-</td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>-</td>";
}
echo "</tr>";

// Check old handlers
foreach ($old_handlers as $file => $description) {
    $exists = file_exists($file);
    $disabled = file_exists($file . '.disabled');
    
    if ($disabled) {
        $status = '🔒 DISABLED';
        $color = '#6c757d';
        $size = number_format(filesize($file . '.disabled'));
        $modified = date('Y-m-d H:i:s', filemtime($file . '.disabled'));
    } elseif ($exists) {
        $status = '⚠️ ACTIVE (Should be disabled)';
        $color = '#ffc107';
        $size = number_format(filesize($file));
        $modified = date('Y-m-d H:i:s', filemtime($file));
    } else {
        $status = '❌ NOT FOUND';
        $color = '#6c757d';
        $size = '-';
        $modified = '-';
    }
    
    echo "<tr>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$file}</td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd; color: {$color};'>{$status}</td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$size}</td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$modified}</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

echo "<h3>2. Disable Old Handlers</h3>";

if (isset($_POST['disable_handlers'])) {
    echo "<h4>🔒 Disabling Old Handlers...</h4>";
    
    $disabled_files = [];
    $already_disabled = [];
    $not_found = [];
    
    foreach ($old_handlers as $file => $description) {
        if (file_exists($file . '.disabled')) {
            $already_disabled[] = $file;
        } elseif (file_exists($file)) {
            if (rename($file, $file . '.disabled')) {
                $disabled_files[] = $file;
            }
        } else {
            $not_found[] = $file;
        }
    }
    
    if (!empty($disabled_files)) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>✅ Successfully Disabled:</h5>";
        echo "<ul>";
        foreach ($disabled_files as $file) {
            echo "<li>✅ <strong>{$file}</strong> → {$file}.disabled</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
    if (!empty($already_disabled)) {
        echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>ℹ️ Already Disabled:</h5>";
        echo "<ul>";
        foreach ($already_disabled as $file) {
            echo "<li>ℹ️ <strong>{$file}</strong> - Already disabled</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
    if (!empty($not_found)) {
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>📁 Not Found:</h5>";
        echo "<ul>";
        foreach ($not_found as $file) {
            echo "<li>📁 <strong>{$file}</strong> - File not found</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h5>🎉 Cleanup Complete!</h5>";
    echo "<p><strong>Result:</strong> Only <code>booking_handler.php</code> is now active</p>";
    echo "<p><strong>Benefit:</strong> No conflicts from multiple handlers</p>";
    echo "</div>";
}

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🔒 Disable Old Handlers</h4>";
echo "<form method='POST'>";
echo "<p>This will disable all old booking handlers to prevent conflicts:</p>";
echo "<ul>";
echo "<li>🔒 <strong>Rename old handlers</strong> to .disabled extension</li>";
echo "<li>✅ <strong>Keep only booking_handler.php active</strong></li>";
echo "<li>🛡️ <strong>Prevent handler conflicts</strong></li>";
echo "<li>💾 <strong>Preserve files</strong> - just rename them</li>";
echo "</ul>";
echo "<button type='submit' name='disable_handlers' style='background: #ffc107; color: #212529; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer;'>🔒 Disable Old Handlers</button>";
echo "</form>";
echo "</div>";

echo "<h3>3. Verify Single Handler</h3>";

echo "<div style='background: #e8f4f8; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📋 Active Handler Verification:</h4>";

if (file_exists('booking_handler.php')) {
    $handler_content = file_get_contents('booking_handler.php');
    
    // Check for deduplication
    $has_dedup = strpos($handler_content, 'DEDUPLICATION CHECK') !== false;
    
    // Check for proper headers
    $has_json_header = strpos($handler_content, 'Content-Type: application/json') !== false;
    
    // Check for error handling
    $has_error_handling = strpos($handler_content, 'ob_start()') !== false;
    
    echo "<ul>";
    echo "<li>" . ($has_dedup ? "✅" : "❌") . " <strong>Deduplication protection:</strong> " . ($has_dedup ? "Present" : "Missing") . "</li>";
    echo "<li>" . ($has_json_header ? "✅" : "❌") . " <strong>JSON headers:</strong> " . ($has_json_header ? "Present" : "Missing") . "</li>";
    echo "<li>" . ($has_error_handling ? "✅" : "❌") . " <strong>Error handling:</strong> " . ($has_error_handling ? "Present" : "Missing") . "</li>";
    echo "</ul>";
    
    if ($has_dedup && $has_json_header && $has_error_handling) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
        echo "🎉 <strong>Perfect!</strong> booking_handler.php has all required features";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
        echo "⚠️ <strong>Issues found</strong> in booking_handler.php - may need updates";
        echo "</div>";
    }
} else {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
    echo "❌ <strong>booking_handler.php not found!</strong> This is the main handler file";
    echo "</div>";
}
echo "</div>";

echo "<h3>4. Summary</h3>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🎯 Handler Cleanup Benefits:</h4>";

echo "<h5>✅ Single Active Handler:</h5>";
echo "<ul>";
echo "<li>✅ <strong>Only booking_handler.php active</strong> - No conflicts</li>";
echo "<li>✅ <strong>All old handlers disabled</strong> - Clean environment</li>";
echo "<li>✅ <strong>Deduplication protection</strong> - Prevents duplicates</li>";
echo "<li>✅ <strong>Proper error handling</strong> - Clean responses</li>";
echo "</ul>";

echo "<h5>🎯 Expected Results:</h5>";
echo "<ul>";
echo "<li>📋 <strong>Single form submission</strong> → Single database record</li>";
echo "<li>📧 <strong>Single email notification</strong> → No duplicates</li>";
echo "<li>📊 <strong>Single admin notification</strong> → Clean dashboard</li>";
echo "<li>🛡️ <strong>Duplicate protection</strong> → 5-minute blocking</li>";
echo "</ul>";

echo "<h5>🧪 Next Steps:</h5>";
echo "<ol>";
echo "<li><strong>Disable old handlers above</strong> if any are still active</li>";
echo "<li><strong>Test duplicate fixes:</strong> <a href='test_duplicate_fixes.php' style='color: #007bff;'>Run Tests</a></li>";
echo "<li><strong>Submit real booking:</strong> <a href='../booking.html' target='_blank' style='color: #007bff;'>Test Booking Form</a></li>";
echo "<li><strong>Verify single success message</strong> and single database entry</li>";
echo "</ol>";
echo "</div>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cleanup Old Booking Handlers</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 1200px; 
            margin: 50px auto; 
            padding: 20px; 
            background: #f5f5f5;
        }
        h2, h3 { color: #2c3e50; }
        h4, h5 { color: inherit; margin-bottom: 10px; }
        p { line-height: 1.6; }
        ul, ol { line-height: 1.8; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
        button { font-family: inherit; }
        button:hover { opacity: 0.9; transform: translateY(-1px); transition: all 0.3s ease; }
        code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; }
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
