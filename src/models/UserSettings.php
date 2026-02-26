<?php

require_once __DIR__ . '/../../config/database.php';

class UserSettings {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function get(int $userId, string $key, $default = null) {
        $stmt = $this->db->prepare(
            'SELECT setting_value FROM user_settings WHERE user_id = ? AND setting_key = ? LIMIT 1'
        );
        $stmt->execute([$userId, $key]);
        $result = $stmt->fetch(PDO::FETCH_COLUMN);
        
        if ($result === false) {
            return $default;
        }
        
        // Try to decode JSON values
        $decoded = json_decode($result, true);
        return $decoded !== null ? $decoded : $result;
    }

    public function set(int $userId, string $key, $value): bool {
        // Encode arrays/objects as JSON
        $encodedValue = is_array($value) || is_object($value) 
            ? json_encode($value) 
            : (string)$value;

        $stmt = $this->db->prepare(
            'INSERT INTO user_settings (user_id, setting_key, setting_value)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        return $stmt->execute([$userId, $key, $encodedValue]);
    }

    public function getAll(int $userId): array {
        $stmt = $this->db->prepare(
            'SELECT setting_key, setting_value FROM user_settings WHERE user_id = ?'
        );
        $stmt->execute([$userId]);
        
        $settings = [];
        while ($row = $stmt->fetch()) {
            $decoded = json_decode($row['setting_value'], true);
            $settings[$row['setting_key']] = $decoded !== null ? $decoded : $row['setting_value'];
        }
        return $settings;
    }

    public function delete(int $userId, string $key): bool {
        $stmt = $this->db->prepare(
            'DELETE FROM user_settings WHERE user_id = ? AND setting_key = ?'
        );
        return $stmt->execute([$userId, $key]);
    }

    // Get notification preferences with defaults
    public function getNotificationPreferences(int $userId): array {
        return [
            'enabled' => $this->get($userId, 'notifications_enabled', true),
            'email_enabled' => $this->get($userId, 'notifications_email', false),
            'sms_enabled' => $this->get($userId, 'notifications_sms', false),
            'in_app_enabled' => $this->get($userId, 'notifications_in_app', true),
            'transaction_due' => $this->get($userId, 'notify_transaction_due', true),
            'recurring_due' => $this->get($userId, 'notify_recurring_due', true),
            'low_balance' => $this->get($userId, 'notify_low_balance', true),
            'days_before' => $this->get($userId, 'notify_days_before', 1),
            'quiet_hours_start' => $this->get($userId, 'quiet_hours_start', '22:00'),
            'quiet_hours_end' => $this->get($userId, 'quiet_hours_end', '08:00'),
        ];
    }

    public function saveNotificationPreferences(int $userId, array $prefs): bool {
        $success = true;
        foreach ($prefs as $key => $value) {
            $prefKey = strpos($key, 'notify_') === 0 || strpos($key, 'notifications_') === 0 || strpos($key, 'quiet_') === 0
                ? $key 
                : 'notify_' . $key;
            $success = $this->set($userId, $prefKey, $value) && $success;
        }
        return $success;
    }
}
