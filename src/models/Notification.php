<?php

require_once __DIR__ . '/../../config/database.php';

class Notification {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create(int $userId, array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO notifications (user_id, type, title, message, related_transaction_id, related_account_id)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $data['type'] ?? 'system',
            $data['title'],
            $data['message'],
            $data['related_transaction_id'] ?? null,
            $data['related_account_id'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getUnread(int $userId, int $limit = 20): array {
        $stmt = $this->db->prepare(
            'SELECT * FROM notifications 
             WHERE user_id = ? AND is_read = 0 
             ORDER BY created_at DESC 
             LIMIT ?'
        );
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    public function getAll(int $userId, int $limit = 50): array {
        $stmt = $this->db->prepare(
            'SELECT * FROM notifications 
             WHERE user_id = ? 
             ORDER BY created_at DESC 
             LIMIT ?'
        );
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    public function markAsRead(int $id, int $userId): bool {
        $stmt = $this->db->prepare(
            'UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([$id, $userId]);
    }

    public function markAllAsRead(int $userId): bool {
        $stmt = $this->db->prepare(
            'UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0'
        );
        return $stmt->execute([$userId]);
    }

    public function getUnreadCount(int $userId): int {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0'
        );
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare(
            'DELETE FROM notifications WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([$id, $userId]);
    }

    public function deleteAll(int $userId): bool {
        $stmt = $this->db->prepare(
            'DELETE FROM notifications WHERE user_id = ?'
        );
        return $stmt->execute([$userId]);
    }
}
