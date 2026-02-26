<?php

require_once __DIR__ . '/../../config/database.php';

class Transaction {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function allForUser(int $userId, array $filters = []): array {
        $where  = ['t.user_id = ?'];
        $params = [$userId];

        if (!empty($filters['account_id'])) {
            $where[]  = 't.account_id = ?';
            $params[] = $filters['account_id'];
        }
        if (!empty($filters['type'])) {
            $where[]  = 't.type = ?';
            $params[] = $filters['type'];
        }
        if (!empty($filters['date_from'])) {
            $where[]  = 't.transaction_date >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[]  = 't.transaction_date <= ?';
            $params[] = $filters['date_to'];
        }

        $sql = "SELECT t.*, a.name AS account_name, a.currency
                FROM transactions t
                LEFT JOIN accounts a ON a.id = t.account_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY t.transaction_date DESC, t.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * All transactions for a specific month (YYYY-MM), across all accounts.
     */
    public function byMonth(int $userId, int $year, int $month): array {
        $from = sprintf('%04d-%02d-01', $year, $month);
        $to   = date('Y-m-t', strtotime($from));

        $stmt = $this->db->prepare(
            "SELECT t.*, a.name AS account_name, a.currency
             FROM transactions t
             LEFT JOIN accounts a ON a.id = t.account_id
             WHERE t.user_id = ? AND t.transaction_date BETWEEN ? AND ?
             ORDER BY t.transaction_date ASC, t.id ASC"
        );
        $stmt->execute([$userId, $from, $to]);
        return $stmt->fetchAll();
    }

    public function find(int $id, int $userId): array|false {
        $stmt = $this->db->prepare(
            "SELECT t.*, a.name AS account_name
             FROM transactions t
             LEFT JOIN accounts a ON a.id = t.account_id
             WHERE t.id = ? AND t.user_id = ? LIMIT 1"
        );
        $stmt->execute([$id, $userId]);
        return $stmt->fetch();
    }

    public function create(int $userId, array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO transactions (user_id, account_id, type, amount, description, transaction_date, processed, notify_on_date)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $data['account_id'],
            $data['type'],
            $data['amount'],
            $data['description'],
            $data['transaction_date'],
            $data['processed'] ?? 0,
            $data['notify_on_date'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, int $userId, array $data): bool {
        $stmt = $this->db->prepare(
            'UPDATE transactions SET account_id=?, type=?, amount=?, description=?, transaction_date=?, processed=?, notify_on_date=?
             WHERE id=? AND user_id=?'
        );
        return $stmt->execute([
            $data['account_id'],
            $data['type'],
            $data['amount'],
            $data['description'],
            $data['transaction_date'],
            $data['processed'] ?? 0,
            $data['notify_on_date'] ?? 0,
            $id,
            $userId,
        ]);
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare(
            'DELETE FROM transactions WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([$id, $userId]);
    }

    public function markAsProcessed(int $id, int $userId): bool {
        $stmt = $this->db->prepare(
            'UPDATE transactions SET processed = 1 WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([$id, $userId]);
    }
}
