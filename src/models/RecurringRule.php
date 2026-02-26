<?php

require_once __DIR__ . '/../../config/database.php';

class RecurringRule {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function allForUser(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT r.*, a.name AS account_name
             FROM recurring_rules r
             LEFT JOIN accounts a ON a.id = r.account_id
             WHERE r.user_id = ?
             ORDER BY r.description ASC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function find(int $id, int $userId): array|false {
        $stmt = $this->db->prepare(
            'SELECT * FROM recurring_rules WHERE id = ? AND user_id = ? LIMIT 1'
        );
        $stmt->execute([$id, $userId]);
        return $stmt->fetch();
    }

    public function create(int $userId, array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO recurring_rules
             (user_id, account_id, type, amount, description, frequency, day_of_month, start_date, end_date)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $data['account_id'],
            $data['type'],
            $data['amount'],
            $data['description'],
            $data['frequency'],
            $data['day_of_month'] ?: null,
            $data['start_date'],
            $data['end_date'] ?: null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, int $userId, array $data): bool {
        $stmt = $this->db->prepare(
            'UPDATE recurring_rules SET account_id=?, type=?, amount=?,
             description=?, frequency=?, day_of_month=?, start_date=?, end_date=?, notify_before_days=?
             WHERE id=? AND user_id=?'
        );
        return $stmt->execute([
            $data['account_id'],
            $data['type'],
            $data['amount'],
            $data['description'],
            $data['frequency'],
            $data['day_of_month'] ?: null,
            $data['start_date'],
            $data['end_date'] ?: null,
            $data['notify_before_days'] ?? 0,
            $id,
            $userId,
        ]);
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare(
            'DELETE FROM recurring_rules WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([$id, $userId]);
    }

    /**
     * Generate virtual transaction-shaped rows for a given month/year.
     * These are NOT stored in the DB — used only for timeline display.
     */
    public function generateForMonth(int $userId, int $year, int $month): array {
        $rules      = $this->allForUser($userId);
        $monthStart = mktime(0, 0, 0, $month, 1, $year);
        $monthEnd   = mktime(23, 59, 59, $month, (int)date('t', $monthStart), $year);
        $occurrences = [];

        foreach ($rules as $rule) {
            $start = strtotime($rule['start_date']);
            $end   = $rule['end_date'] ? strtotime($rule['end_date']) : PHP_INT_MAX;

            // Skip rules that haven't started or have already ended
            if ($start > $monthEnd || $end < $monthStart) continue;

            $dates = $this->getOccurrenceDatesInMonth($rule, $year, $month, $monthStart, $monthEnd);

            foreach ($dates as $date) {
                $ts = strtotime($date);
                if ($ts < $start || $ts > $end) continue;

                // Check no real transaction exists on same day with same description & account
                // (to avoid double-counting if user manually entered it)
                $occurrences[] = [
                    'id'               => 'r' . $rule['id'],   // prefix to distinguish from real
                    'rule_id'          => $rule['id'],
                    'user_id'          => $userId,
                    'account_id'       => $rule['account_id'],
                    'account_name'     => $rule['account_name'],
                    'type'             => $rule['type'],
                    'amount'           => $rule['amount'],
                    'description'      => $rule['description'],
                    'transaction_date' => $date,
                    'is_recurring'     => true,
                ];
            }
        }

        return $occurrences;
    }

    private function getOccurrenceDatesInMonth(array $rule, int $year, int $month, int $monthStart, int $monthEnd): array {
        $dates = [];

        switch ($rule['frequency']) {
            case 'monthly':
                $day = $rule['day_of_month'] ?: (int)date('d', strtotime($rule['start_date']));
                // Clamp to last day of month
                $daysInMonth = (int)date('t', $monthStart);
                $day = min($day, $daysInMonth);
                $dates[] = sprintf('%04d-%02d-%02d', $year, $month, $day);
                break;

            case 'weekly':
                $startDow = (int)date('N', strtotime($rule['start_date'])); // 1=Mon
                $cur = $monthStart;
                while ($cur <= $monthEnd) {
                    if ((int)date('N', $cur) === $startDow) {
                        $dates[] = date('Y-m-d', $cur);
                    }
                    $cur = strtotime('+1 day', $cur);
                }
                break;

            case 'biweekly':
                // Use DateTime for all arithmetic to avoid DST-induced timestamp errors
                $startDt      = new DateTime($rule['start_date']);
                $monthStartDt = new DateTime(sprintf('%04d-%02d-01', $year, $month));
                $monthEndDt   = new DateTime(date('Y-m-t', $monthStart)); // last day of month

                $cur = clone $startDt;

                // Fast-forward close to the month start without overshooting
                if ($cur < $monthStartDt) {
                    $diffDays = (int)$startDt->diff($monthStartDt)->days;
                    $periods  = max(0, (int)floor($diffDays / 14) - 1);
                    if ($periods > 0) $cur->modify('+' . ($periods * 14) . ' days');
                }

                // Step forward until we reach the month
                while ($cur < $monthStartDt) {
                    $cur->modify('+14 days');
                }

                // Collect all occurrences inside the month
                while ($cur <= $monthEndDt) {
                    $dates[] = $cur->format('Y-m-d');
                    $cur->modify('+14 days');
                }
                break;

            case 'daily':
                $cur = $monthStart;
                while ($cur <= $monthEnd) {
                    $dates[] = date('Y-m-d', $cur);
                    $cur = strtotime('+1 day', $cur);
                }
                break;

            case 'yearly':
                $origDate = date('m-d', strtotime($rule['start_date']));
                $candidate = sprintf('%04d-%s', $year, $origDate);
                $ts = strtotime($candidate);
                if ($ts >= $monthStart && $ts <= $monthEnd) {
                    $dates[] = $candidate;
                }
                break;
        }

        return $dates;
    }
}
