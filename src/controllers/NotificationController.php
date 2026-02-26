<?php

require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/UserSettings.php';

class NotificationController {
    private Notification $notificationModel;
    private UserSettings $settingsModel;

    public function __construct() {
        $this->notificationModel = new Notification();
        $this->settingsModel = new UserSettings();
    }

    public function getUnreadCount(int $userId): int {
        return $this->notificationModel->getUnreadCount($userId);
    }

    public function getRecent(int $userId, int $limit = 10): array {
        return $this->notificationModel->getAll($userId, $limit);
    }

    public function markAsRead(int $id, int $userId): bool {
        return $this->notificationModel->markAsRead($id, $userId);
    }

    public function markAllAsRead(int $userId): bool {
        return $this->notificationModel->markAllAsRead($userId);
    }

    public function create(int $userId, string $type, string $title, string $message, ?int $transactionId = null, ?int $accountId = null): int {
        // Check if notifications are enabled
        $prefs = $this->settingsModel->getNotificationPreferences($userId);
        if (!$prefs['enabled']) {
            return 0;
        }

        // Check if this type of notification is enabled
        $typeEnabled = match($type) {
            'transaction_due' => $prefs['transaction_due'],
            'recurring_due' => $prefs['recurring_due'],
            'low_balance' => $prefs['low_balance'],
            default => true
        };

        if (!$typeEnabled) {
            return 0;
        }

        // Create in-app notification if enabled
        $notificationId = 0;
        if ($prefs['in_app_enabled']) {
            $notificationId = $this->notificationModel->create($userId, [
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'related_transaction_id' => $transactionId,
                'related_account_id' => $accountId,
            ]);
        }
        
        // Send email notification if enabled
        if ($prefs['email_enabled']) {
            $this->sendEmailNotification($userId, $title, $message);
        }
        
        // Send SMS notification if enabled
        if ($prefs['sms_enabled']) {
            $this->sendSmsNotification($userId, $title, $message);
        }
        
        return $notificationId;
    }
    
    private function sendEmailNotification(int $userId, string $title, string $message): void {
        require_once __DIR__ . '/../services/EmailService.php';
        require_once __DIR__ . '/../models/User.php';
        
        $userModel = new User();
        $user = $userModel->find($userId);
        
        if (empty($user['email'])) {
            return;
        }
        
        $emailService = new EmailService();
        $actionUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/cashtimeline/public/notifications';
        $emailService->sendNotification($user['email'], $title, $message, $actionUrl, 'View Notifications');
    }
    
    private function sendSmsNotification(int $userId, string $title, string $message): void {
        require_once __DIR__ . '/../services/SmsService.php';
        require_once __DIR__ . '/../models/User.php';
        
        $userModel = new User();
        $user = $userModel->find($userId);
        
        if (empty($user['phone'])) {
            return;
        }
        
        $smsService = new SmsService();
        $smsService->sendNotification($user['phone'], $title, $message);
    }

    public function delete(int $id, int $userId): bool {
        return $this->notificationModel->delete($id, $userId);
    }

    public function deleteAll(int $userId): bool {
        return $this->notificationModel->deleteAll($userId);
    }

    // Check for upcoming transactions and create notifications
    public static function checkUpcomingTransactions(int $userId): int {
        require_once __DIR__ . '/../models/Transaction.php';
        require_once __DIR__ . '/../models/RecurringRule.php';
        require_once __DIR__ . '/../models/Account.php';
        require_once __DIR__ . '/../models/UserSettings.php';

        $controller = new self();
        $settingsModel = new UserSettings();
        $prefs = $settingsModel->getNotificationPreferences($userId);
        
        if (!$prefs['enabled']) {
            return 0;
        }

        $notificationsSent = 0;
        $daysAhead = $prefs['days_before'];
        $targetDate = date('Y-m-d', strtotime("+{$daysAhead} days"));
        
        $db = Database::getInstance();

        // Check one-time transactions
        if ($prefs['transaction_due']) {
            $stmt = $db->prepare(
                'SELECT t.*, a.name as account_name 
                 FROM transactions t
                 JOIN accounts a ON t.account_id = a.id
                 WHERE t.user_id = ? 
                 AND t.transaction_date = ? 
                 AND t.processed = 0
                 AND t.notify_on_date = 1
                 AND NOT EXISTS (
                     SELECT 1 FROM notifications 
                     WHERE user_id = ? 
                     AND type = "transaction_due" 
                     AND related_transaction_id = t.id
                     AND DATE(created_at) = CURDATE()
                 )'
            );
            $stmt->execute([$userId, $targetDate, $userId]);
            $transactions = $stmt->fetchAll();

            foreach ($transactions as $txn) {
                $amount = number_format(abs($txn['amount']), 2);
                $type = $txn['amount'] < 0 ? 'expense' : 'income';
                $title = $daysAhead == 0 ? 'Transaction Due Today' : "Transaction Due in {$daysAhead} " . ($daysAhead == 1 ? 'Day' : 'Days');
                $message = "{$txn['description']} - \${$amount} ({$txn['account_name']})";
                
                $controller->create($userId, 'transaction_due', $title, $message, $txn['id'], $txn['account_id']);
                $notificationsSent++;
            }
        }

        // Check recurring transactions
        if ($prefs['recurring_due']) {
            $recurringModel = new RecurringRule();
            $rules = $recurringModel->allForUser($userId);
            
            foreach ($rules as $rule) {
                // Generate occurrences for the target date's month
                $year = (int)date('Y', strtotime($targetDate));
                $month = (int)date('m', strtotime($targetDate));
                $occurrences = $recurringModel->generateForMonth($userId, $year, $month);
                
                // Check if any occurrence matches our target date
                $hasOccurrence = false;
                foreach ($occurrences as $occ) {
                    if ($occ['transaction_date'] === $targetDate && $occ['recurring_rule_id'] == $rule['id']) {
                        $hasOccurrence = true;
                        break;
                    }
                }
                
                if ($hasOccurrence) {
                    // Check if we already notified today
                    $stmt = $db->prepare(
                        'SELECT COUNT(*) FROM notifications 
                         WHERE user_id = ? 
                         AND type = "recurring_due"
                         AND message LIKE ?
                         AND DATE(created_at) = CURDATE()'
                    );
                    $stmt->execute([$userId, '%' . $rule['description'] . '%']);
                    $alreadyNotified = $stmt->fetchColumn();
                    
                    if (!$alreadyNotified) {
                        $amount = number_format(abs($rule['amount']), 2);
                        $title = $daysAhead == 0 ? 'Recurring Transaction Due Today' : "Recurring Transaction Due in {$daysAhead} " . ($daysAhead == 1 ? 'Day' : 'Days');
                        $message = "{$rule['description']} - \${$amount} ({$rule['frequency']})";
                        
                        $controller->create($userId, 'recurring_due', $title, $message, null, $rule['account_id']);
                        $notificationsSent++;
                    }
                }
            }
        }

        return $notificationsSent;
    }
}
