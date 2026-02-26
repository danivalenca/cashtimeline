<?php
/**
 * Notification Scheduler
 * 
 * Run this script via cron to check for upcoming transactions and send notifications.
 * 
 * Recommended cron setup (run every hour):
 * 0 * * * * /usr/bin/php /path/to/cashtimeline/src/jobs/notification_scheduler.php
 * 
 * Or run multiple times per day:
 * 0 8,12,18 * * * /usr/bin/php /path/to/cashtimeline/src/jobs/notification_scheduler.php
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../controllers/NotificationController.php';
require_once __DIR__ . '/../models/User.php';

// Prevent running from browser
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line.');
}

echo "[" . date('Y-m-d H:i:s') . "] Starting notification scheduler...\n";

try {
    $db = Database::getInstance();
    
    // Get all active users
    $stmt = $db->query('SELECT id FROM users');
    $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $totalNotifications = 0;
    
    foreach ($users as $userId) {
        echo "  Checking notifications for user ID {$userId}... ";
        $count = NotificationController::checkUpcomingTransactions($userId);
        $totalNotifications += $count;
        echo "{$count} notifications created\n";
    }
    
    echo "[" . date('Y-m-d H:i:s') . "] Completed. Total notifications created: {$totalNotifications}\n";
    
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
