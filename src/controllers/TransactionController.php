<?php

require_once __DIR__ . '/../models/Transaction.php';

class TransactionController {
    private Transaction $model;

    public function __construct() {
        $this->model = new Transaction();
    }

    public function handlePost(int $userId): void {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'create':
                $data = $this->extractData();
                if (!$this->validate($data)) {
                    $this->flash('Invalid transaction data.', 'danger');
                    break;
                }
                $this->model->create($userId, $data);
                $this->flash('Transaction added.', 'success');
                break;

            case 'duplicate':
                $sourceId = (int)($_POST['id'] ?? 0);
                $source   = $this->model->find($sourceId, $userId);
                if (!$source) {
                    $this->flash('Transaction not found.', 'danger');
                    break;
                }
                $data = [
                    'account_id'       => $source['account_id'],
                    'type'             => $source['type'],
                    'amount'           => $source['amount'],
                    'description'      => $source['description'],
                    'transaction_date' => $_POST['transaction_date'] ?? $source['transaction_date'],
                ];
                $this->model->create($userId, $data);
                $this->flash('Transaction duplicated.', 'success');
                break;

            case 'update':
                $id   = (int)($_POST['id'] ?? 0);
                $data = $this->extractData();
                if (!$this->model->find($id, $userId)) {
                    $this->flash('Transaction not found.', 'danger');
                    break;
                }
                $this->model->update($id, $userId, $data);
                $this->flash('Transaction updated.', 'success');
                break;

            case 'delete':
                $id = (int)($_POST['id'] ?? 0);
                $this->model->delete($id, $userId);
                $this->flash('Transaction deleted.', 'success');
                break;

            case 'process':
                // Process transaction: update account balance and mark as processed
                $id = (int)($_POST['id'] ?? 0);
                $txn = $this->model->find($id, $userId);
                if (!$txn) {
                    $this->flash('Transaction not found.', 'danger');
                    break;
                }
                
                // Get account
                require_once __DIR__ . '/../models/Account.php';
                $accModel = new Account();
                $account = $accModel->find((int)$txn['account_id'], $userId);
                if (!$account) {
                    $this->flash('Account not found.', 'danger');
                    break;
                }
                
                // Update account balance
                $currentBalance = (float)$account['initial_balance'];
                $amount = (float)$txn['amount'];
                $newBalance = $txn['type'] === 'income' 
                    ? $currentBalance + $amount 
                    : $currentBalance - $amount;
                
                $accModel->update(
                    (int)$txn['account_id'],
                    $userId,
                    [
                        'name' => $account['name'],
                        'type' => $account['type'],
                        'currency' => $account['currency'],
                        'initial_balance' => $newBalance,
                        'color' => $account['color'],
                        'sort_order' => $account['sort_order'],
                        'exchange_rate' => $account['exchange_rate'],
                    ]
                );
                
                // Mark as processed instead of deleting
                $this->model->markAsProcessed($id, $userId);
                
                $this->flash('Transaction processed and account balance updated.', 'success');
                break;

            case 'process_recurring':
                // Process recurring occurrence: create transaction and mark as processed
                $data = $this->extractData();
                if (!$this->validate($data)) {
                    $this->flash('Invalid transaction data.', 'danger');
                    break;
                }
                
                // Create the transaction
                $txnId = $this->model->create($userId, $data);
                
                // Get account
                require_once __DIR__ . '/../models/Account.php';
                $accModel = new Account();
                $account = $accModel->find((int)$data['account_id'], $userId);
                if ($account) {
                    // Update account balance
                    $currentBalance = (float)$account['initial_balance'];
                    $amount = (float)$data['amount'];
                    $newBalance = $data['type'] === 'income' 
                        ? $currentBalance + $amount 
                        : $currentBalance - $amount;
                    
                    $accModel->update(
                        (int)$data['account_id'],
                        $userId,
                        [
                            'name' => $account['name'],
                            'type' => $account['type'],
                            'currency' => $account['currency'],
                            'initial_balance' => $newBalance,
                            'color' => $account['color'],
                            'sort_order' => $account['sort_order'],
                            'exchange_rate' => $account['exchange_rate'],
                        ]
                    );
                    
                    // Mark as processed
                    $this->model->markAsProcessed($txnId, $userId);
                }
                
                $this->flash('Recurring transaction processed and account balance updated.', 'success');
                break;

            default:
                $this->flash('Unknown action.', 'danger');
        }

        $redirect = $_POST['redirect'] ?? '/cashtimeline/public/dashboard';
        header('Location: ' . $redirect);
        exit;
    }

    public function getJson(int $id, int $userId): void {
        $txn = $this->model->find($id, $userId);
        header('Content-Type: application/json');
        echo json_encode($txn ?: []);
        exit;
    }

    private function extractData(): array {
        return [
            'account_id'       => (int)($_POST['account_id'] ?? 0),
            'type'             => $_POST['type'] ?? 'expense',
            'amount'           => abs((float)($_POST['amount'] ?? 0)),
            'description'      => trim($_POST['description'] ?? ''),
            'transaction_date' => $_POST['transaction_date'] ?? date('Y-m-d'),
            'notify_on_date'   => isset($_POST['notify_on_date']) ? 1 : 0,
        ];
    }

    private function validate(array $data): bool {
        return $data['account_id'] > 0
            && $data['amount'] > 0
            && !empty($data['description'])
            && !empty($data['transaction_date']);
    }

    private function flash(string $msg, string $type): void {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
    }
}
