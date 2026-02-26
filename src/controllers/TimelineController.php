<?php

require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../models/RecurringRule.php';
require_once __DIR__ . '/../models/Account.php';

class TimelineController {
    private Transaction   $txnModel;
    private RecurringRule $recurModel;
    private Account       $accModel;

    public function __construct() {
        $this->txnModel   = new Transaction();
        $this->recurModel = new RecurringRule();
        $this->accModel   = new Account();
    }

    /**
     * Build timeline data for N months starting at $startYear/$startMonth.
     * Returns an array of month columns, each with:
     *   - label, year, month
     *   - rows[] (merged real + recurring transactions, date-sorted)
     *   - net_worth (total across all accounts at end of month)
     */
    public function buildTimeline(int $userId, int $startYear, int $startMonth, int $count = 6): array {
        $columns = [];

        // Build exchange-rate map once (account_id => rate)
        $exchangeRates = [];
        foreach ($this->accModel->allForUser($userId) as $acc) {
            $exchangeRates[(int)$acc['id']] = (float)($acc['exchange_rate'] ?? 1.0);
        }

        $today        = date('Y-m-d');
        $todayYm      = (int)date('Ym'); // e.g. 202602

        // Pre-compute projected end-of-current-month net worth so that future months
        // always compound from the correct base, regardless of which month the
        // displayed range starts at (avoids skipping remaining days of the current month).
        $curY  = (int)date('Y');
        $curM  = (int)date('n');
        $currentMonthEndNetWorth = $this->accModel->netWorthAtDate($userId, $today);
        $curReal      = $this->txnModel->byMonth($userId, $curY, $curM);
        $curRecurring = $this->recurModel->generateForMonth($userId, $curY, $curM);
        foreach ($this->mergeRows($curReal, $curRecurring) as $row) {
            // Skip processed transactions
            $isProcessed = !empty($row['processed']);
            if ($isProcessed) {
                continue;
            }
            
            $isRecurring = !empty($row['is_recurring']);
            if ($row['transaction_date'] > $today || ($isRecurring && $row['transaction_date'] === $today)) {
                $rate = $exchangeRates[(int)$row['account_id']] ?? 1.0;
                $delta = (float)$row['amount'] * $rate;
                $currentMonthEndNetWorth += $row['type'] === 'income' ? $delta : -$delta;
            }
        }

        // Pre-compute carry-forward through any months between the current month
        // and the first displayed month, so that e.g. jumping straight to April
        // still correctly compounds February → March → April.
        $prevNetWorth = $currentMonthEndNetWorth;
        $walkY = $curY;
        $walkM = $curM + 1; // start from the month AFTER the current month
        while ($walkM > 12) { $walkM -= 12; $walkY++; }
        while ($walkY < $startYear || ($walkY === $startYear && $walkM < $startMonth)) {
            $walkReal      = $this->txnModel->byMonth($userId, $walkY, $walkM);
            $walkRecurring = $this->recurModel->generateForMonth($userId, $walkY, $walkM);
            foreach ($this->mergeRows($walkReal, $walkRecurring) as $row) {
                // Skip processed transactions
                $isProcessed = !empty($row['processed']);
                if ($isProcessed) {
                    continue;
                }
                
                $rate          = $exchangeRates[(int)$row['account_id']] ?? 1.0;
                $delta         = (float)$row['amount'] * $rate;
                $prevNetWorth += $row['type'] === 'income' ? $delta : -$delta;
            }
            $walkM++;
            if ($walkM > 12) { $walkM = 1; $walkY++; }
        }

        for ($i = 0; $i < $count; $i++) {
            $m = $startMonth + $i;
            $y = $startYear;
            while ($m > 12) { $m -= 12; $y++; }

            $real      = $this->txnModel->byMonth($userId, $y, $m);
            $recurring = $this->recurModel->generateForMonth($userId, $y, $m);

            // Merge and remove recurring entries that already have a real transaction
            $rows = $this->mergeRows($real, $recurring);

            // Sort by date asc, then description
            usort($rows, fn($a, $b) =>
                strcmp($a['transaction_date'], $b['transaction_date']) ?:
                strcmp($a['description'], $b['description'])
            );

            $colYm       = (int)sprintf('%04d%02d', $y, $m);
            $lastDayStr  = date('Y-m-t', mktime(0, 0, 0, $m, 1, $y));

            if ($colYm < $todayYm) {
                // ── Past month: DB is authoritative, no projections needed ──
                $netWorth = $this->accModel->netWorthAtDate($userId, $lastDayStr);

            } elseif ($colYm === $todayYm) {
                // ── Current month: DB balance as of today + all future rows (real or recurring) ──
                // netWorthAtDate covers everything up to and including today.
                // mergeRows() already deduplicates, so no double-counting occurs.
                $netWorth = $this->accModel->netWorthAtDate($userId, $today);
                
                // Only count future unprocessed transactions in end balance (but display all rows)
                foreach ($rows as $row) {
                    // Skip processed transactions - they're already in the account balance
                    $isProcessed = !empty($row['processed']);
                    if ($isProcessed) {
                        continue;
                    }
                    
                    // Real transactions on $today are already covered by netWorthAtDate.
                    // Recurring placeholders are never in the DB, so they must be applied
                    // even when dated today (they haven't been "recorded" yet).
                    $isRecurring = !empty($row['is_recurring']);
                    if ($row['transaction_date'] > $today || ($isRecurring && $row['transaction_date'] === $today)) {
                        $rate      = $exchangeRates[(int)$row['account_id']] ?? 1.0;
                        $delta     = (float)$row['amount'] * $rate;
                        $netWorth += $row['type'] === 'income' ? $delta : -$delta;
                    }
                }

            } else {
                // ── Future month: compound on previous month's projected balance ──
                $netWorth = $prevNetWorth;
                foreach ($rows as $row) {
                    // Skip processed transactions
                    $isProcessed = !empty($row['processed']);
                    if ($isProcessed) {
                        continue;
                    }
                    
                    $rate      = $exchangeRates[(int)$row['account_id']] ?? 1.0;
                    $delta     = (float)$row['amount'] * $rate;
                    $netWorth += $row['type'] === 'income' ? $delta : -$delta;
                }
            }

            $prevNetWorth = $netWorth;

            $columns[] = [
                'label'     => date('F Y', mktime(0, 0, 0, $m, 1, $y)),
                'year'      => $y,
                'month'     => $m,
                'rows'      => $rows,
                'net_worth' => $netWorth,
            ];
        }

        return $columns;
    }

    /**
     * Merge real transactions with recurring, skipping recurring entries
     * that already have a real transaction with the same account + description + date.
     */
    private function mergeRows(array $real, array $recurring): array {
        // Suppress a recurring placeholder if a real transaction already exists
        // for the same account + description + date (case-insensitive).
        // This covers the "record this occurrence" flow: once saved, the real
        // transaction takes over and the placeholder disappears.
        $realKeys = [];
        foreach ($real as $r) {
            $key = $r['account_id'] . '|' . strtolower($r['description']) . '|' . $r['transaction_date'];
            $realKeys[$key] = true;
        }

        $merged = $real;
        foreach ($recurring as $rec) {
            $key = $rec['account_id'] . '|' . strtolower($rec['description']) . '|' . $rec['transaction_date'];
            if (!isset($realKeys[$key])) {
                $merged[] = $rec;
            }
        }

        return $merged;
    }
}
