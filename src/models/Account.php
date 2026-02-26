<?php

require_once __DIR__ . '/../../config/database.php';

class Account {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function allForUser(int $userId): array {
        $stmt = $this->db->prepare(
            'SELECT * FROM accounts WHERE user_id = ? ORDER BY sort_order ASC, name ASC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function find(int $id, int $userId): array|false {
        $stmt = $this->db->prepare(
            'SELECT * FROM accounts WHERE id = ? AND user_id = ? LIMIT 1'
        );
        $stmt->execute([$id, $userId]);
        return $stmt->fetch();
    }

    public function create(int $userId, array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO accounts (user_id, name, type, currency, initial_balance, color, sort_order, exchange_rate)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $data['name'],
            $data['type'],
            $data['currency'] ?? 'CAD',
            $data['initial_balance'] ?? 0,
            $data['color'] ?? '#6366f1',
            $data['sort_order'] ?? 0,
            $data['exchange_rate'] ?? 1.0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, int $userId, array $data): bool {
        $stmt = $this->db->prepare(
            'UPDATE accounts SET name=?, type=?, currency=?, initial_balance=?, color=?, sort_order=?, exchange_rate=?
             WHERE id=? AND user_id=?'
        );
        return $stmt->execute([
            $data['name'],
            $data['type'],
            $data['currency'] ?? 'CAD',
            $data['initial_balance'] ?? 0,
            $data['color'] ?? '#6366f1',
            $data['sort_order'] ?? 0,
            $data['exchange_rate'] ?? 1.0,
            $id,
            $userId,
        ]);
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare(
            'DELETE FROM accounts WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([$id, $userId]);
    }

    /**
     * Persist a new sort_order for each account given an ordered array of IDs.
     */
    public function reorder(int $userId, array $ids): void {
        $stmt = $this->db->prepare(
            'UPDATE accounts SET sort_order = ? WHERE id = ? AND user_id = ?'
        );
        foreach ($ids as $order => $id) {
            $stmt->execute([$order, (int)$id, $userId]);
        }
    }

    /**
     * Current balance = initial_balance + all income - all expenses up to today.
     */
    public function currentBalance(int $id, int $userId): float {
        return $this->balanceAtDate($id, $userId, date('Y-m-d'));
    }

    /**
     * Balance at end of a given date (inclusive).
     */
    public function balanceAtDate(int $id, int $userId, string $date): float {
        $acc = $this->find($id, $userId);
        if (!$acc) return 0.0;

        $stmt = $this->db->prepare(
            "SELECT
                SUM(CASE WHEN type='income'  THEN amount ELSE 0 END) AS total_in,
                SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS total_out
             FROM transactions
             WHERE account_id = ? AND user_id = ? AND transaction_date <= ? AND processed = 0"
        );
        $stmt->execute([$id, $userId, $date]);
        $row = $stmt->fetch();

        return (float)$acc['initial_balance']
             + (float)($row['total_in']  ?? 0)
             - (float)($row['total_out'] ?? 0);
    }

    /**
     * Net worth across all accounts converted to the user's default currency.
     * Each account's balance is multiplied by its exchange_rate before summing.
     */
    public function netWorthAtDate(int $userId, string $date): float {
        $accounts = $this->allForUser($userId);
        $total = 0.0;
        foreach ($accounts as $acc) {
            $bal   = $this->balanceAtDate($acc['id'], $userId, $date);
            $total += $bal * (float)($acc['exchange_rate'] ?? 1.0);
        }
        return $total;
    }

    /**
     * Find upcoming expense transactions within $days that would overdraw the account.
     * Returns an array of alert arrays with account, description, date, amount, balance, deficit.
     */
    public function upcomingExpenseAlerts(int $userId, int $days = 30): array {
        $today = date('Y-m-d');
        $until = date('Y-m-d', strtotime("+{$days} days"));

        $stmt = $this->db->prepare(
            "SELECT t.*, a.name AS account_name, a.currency
             FROM transactions t
             JOIN accounts a ON a.id = t.account_id
             WHERE t.user_id = ? AND t.type = 'expense'
               AND t.transaction_date > ? AND t.transaction_date <= ?
             ORDER BY t.transaction_date ASC"
        );
        $stmt->execute([$userId, $today, $until]);
        $upcoming = $stmt->fetchAll();

        $alerts = [];
        foreach ($upcoming as $txn) {
            // Balance the day BEFORE this expense lands
            $prevDate = date('Y-m-d', strtotime($txn['transaction_date'] . ' -1 day'));
            $bal = $this->balanceAtDate((int)$txn['account_id'], $userId, $prevDate);
            if ($bal < (float)$txn['amount']) {
                $alerts[] = [
                    'account'     => $txn['account_name'],
                    'currency'    => $txn['currency'],
                    'description' => $txn['description'],
                    'date'        => $txn['transaction_date'],
                    'amount'      => (float)$txn['amount'],
                    'balance'     => $bal,
                    'deficit'     => (float)$txn['amount'] - $bal,
                ];
            }
        }
        return $alerts;
    }
}
