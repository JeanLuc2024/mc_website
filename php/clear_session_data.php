<?php
/**
 * Clear Session Data
 * 
 * This script clears all booking-related session data to reset duplicate prevention
 */

session_start();

// Clear all submission tracking sessions
$cleared_count = 0;
foreach ($_SESSION as $key => $value) {
    if (strpos($key, 'last_submission_') === 0 || strpos($key, 'submission_hash_') === 0) {
        unset($_SESSION[$key]);
        $cleared_count++;
    }
}

echo "✅ Cleared {$cleared_count} session tracking entries\n";
echo "🔄 Duplicate prevention has been reset\n";
echo "📝 Users can now submit bookings normally\n";
?>
