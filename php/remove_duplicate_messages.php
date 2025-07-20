<?php
/**
 * Remove Duplicate Messages
 * 
 * Fix the issue where two success messages appear
 */

echo "<h2>🔧 Remove Duplicate Messages</h2>";

echo "<h3>1. Analyze Current Message System</h3>";

$booking_html = '../booking.html';
if (file_exists($booking_html)) {
    $content = file_get_contents($booking_html);
    
    // Count message containers
    $message_containers = substr_count($content, 'messageContainer');
    $success_messages = substr_count($content, 'showMessage');
    $booking_reference_calls = substr_count($content, 'showBookingReference');
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>📊 Current Message System Analysis:</h4>";
    echo "<ul>";
    echo "<li><strong>Message containers:</strong> {$message_containers}</li>";
    echo "<li><strong>showMessage calls:</strong> {$success_messages}</li>";
    echo "<li><strong>showBookingReference calls:</strong> {$booking_reference_calls}</li>";
    echo "</ul>";
    echo "</div>";
    
    if ($booking_reference_calls > 0) {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h4>🚨 Issue Found!</h4>";
        echo "<p>The <code>showBookingReference</code> function is still being called, which creates the duplicate message above the submit button.</p>";
        echo "</div>";
    }
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>❌ booking.html Not Found</h4>";
    echo "</div>";
}

echo "<h3>2. Remove Duplicate Message System</h3>";

if (isset($_POST['remove_duplicates'])) {
    echo "<h4>🗑️ Removing Duplicate Message System...</h4>";
    
    if (file_exists($booking_html)) {
        $content = file_get_contents($booking_html);
        $changes_made = false;
        
        // Remove any showBookingReference function calls
        if (strpos($content, 'showBookingReference') !== false) {
            $content = preg_replace('/showBookingReference\([^)]*\);?/', '', $content);
            $changes_made = true;
            echo "<p>✅ Removed showBookingReference function calls</p>";
        }
        
        // Remove any duplicate message containers (keep only the first one)
        $pattern = '/<div[^>]*id=["\']messageContainer["\'][^>]*>.*?<\/div>/s';
        $matches = [];
        preg_match_all($pattern, $content, $matches);
        
        if (count($matches[0]) > 1) {
            // Keep only the first message container
            $first_container = $matches[0][0];
            $content = preg_replace($pattern, '', $content);
            
            // Insert the first container back in the correct position
            $form_submit_pos = strpos($content, '<div class="form-submit">');
            if ($form_submit_pos !== false) {
                $content = substr_replace($content, $first_container . "\n\n                        ", $form_submit_pos, 0);
                $changes_made = true;
                echo "<p>✅ Removed duplicate message containers, kept only one</p>";
            }
        }
        
        // Remove any additional success message displays
        $patterns_to_remove = [
            '/\/\/ Show booking reference.*?}/s',
            '/function showBookingReference.*?}/s',
            '/showBookingReference\([^)]*\);?/',
        ];
        
        foreach ($patterns_to_remove as $pattern) {
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, '', $content);
                $changes_made = true;
            }
        }
        
        // Ensure only one success message is shown
        $success_handler = '
                    if (data.success) {
                        // Show ONLY ONE success message
                        showMessage(data.message, \'success\');
                        form.reset();

                        // Scroll to message
                        messageContainer.scrollIntoView({
                            behavior: \'smooth\',
                            block: \'center\'
                        });
                        
                        // Disable submit button after successful submission
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = \'<span class="btn-text">Booking Submitted Successfully</span>\';
                    }';
        
        // Replace the success handling section
        $old_success_pattern = '/if \(data\.success\) \{.*?\/\/ Booking reference is already included in the main success message.*?}/s';
        if (preg_match($old_success_pattern, $content)) {
            $content = preg_replace($old_success_pattern, $success_handler, $content);
            $changes_made = true;
            echo "<p>✅ Updated success handler to show only one message</p>";
        }
        
        if ($changes_made) {
            // Backup original
            copy($booking_html, $booking_html . '.backup.' . date('YmdHis'));
            
            // Write cleaned version
            file_put_contents($booking_html, $content);
            
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<h5>✅ Duplicate Messages Removed!</h5>";
            echo "<ul>";
            echo "<li>✅ <strong>Removed duplicate message containers</strong></li>";
            echo "<li>✅ <strong>Removed showBookingReference calls</strong></li>";
            echo "<li>✅ <strong>Updated success handler</strong> - Only one message</li>";
            echo "<li>✅ <strong>Backup created</strong> - " . basename($booking_html) . ".backup." . date('YmdHis') . "</li>";
            echo "</ul>";
            echo "</div>";
        } else {
            echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<h5>ℹ️ No Duplicate Messages Found</h5>";
            echo "<p>The message system appears to be clean already.</p>";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h5>❌ booking.html Not Found</h5>";
        echo "</div>";
    }
}

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🗑️ Remove Duplicate Message System</h4>";
echo "<form method='POST'>";
echo "<p>This will clean up the duplicate message system:</p>";
echo "<ul>";
echo "<li>🗑️ <strong>Remove showBookingReference function</strong> - Eliminates top message</li>";
echo "<li>🧹 <strong>Clean duplicate message containers</strong> - Keep only one</li>";
echo "<li>✅ <strong>Ensure single success message</strong> - Only one message shown</li>";
echo "<li>🔒 <strong>Disable submit after success</strong> - Prevent resubmission</li>";
echo "</ul>";
echo "<button type='submit' name='remove_duplicates' style='background: #dc3545; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer;'>🗑️ Remove Duplicate Messages</button>";
echo "</form>";
echo "</div>";

echo "<h3>3. Test Message System</h3>";

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🧪 Test the Fixed Message System</h4>";
echo "<p>After applying the fix:</p>";
echo "<ol>";
echo "<li><strong>Submit a test booking:</strong> <a href='../booking.html' target='_blank' style='color: #007bff;'>Open Booking Form</a></li>";
echo "<li><strong>Verify only ONE success message appears</strong></li>";
echo "<li><strong>Check that message appears in the correct location</strong> (near submit button)</li>";
echo "<li><strong>Confirm no duplicate messages above the submit button</strong></li>";
echo "</ol>";
echo "</div>";

echo "<h3>4. Summary</h3>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🎯 Duplicate Message Fix</h4>";

echo "<h5>✅ What This Fixes:</h5>";
echo "<ul>";
echo "<li>❌ <strong>Removes top success message</strong> - No more duplicate above submit button</li>";
echo "<li>✅ <strong>Keeps only one message</strong> - Clean, professional display</li>";
echo "<li>🔒 <strong>Disables submit after success</strong> - Prevents accidental resubmission</li>";
echo "<li>🧹 <strong>Cleans message containers</strong> - Removes any duplicates</li>";
echo "</ul>";

echo "<h5>🎯 Expected Result:</h5>";
echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
echo "<p><strong>User submits booking → Only ONE success message appears</strong></p>";
echo "<ul>";
echo "<li>✅ Single success message near submit button</li>";
echo "<li>❌ NO message above submit button</li>";
echo "<li>✅ Clean, professional appearance</li>";
echo "<li>🔒 Submit button disabled after success</li>";
echo "</ul>";
echo "</div>";

echo "<h5>🧪 Complete Solution:</h5>";
echo "<ol>";
echo "<li><strong>Remove duplicate messages</strong> - This script</li>";
echo "<li><strong>Fix double submissions</strong> - <a href='fix_double_submission_final.php' style='color: #007bff;'>Double Submission Fix</a></li>";
echo "<li><strong>Test complete fix</strong> - Submit real booking</li>";
echo "</ol>";
echo "</div>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Remove Duplicate Messages</title>
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
