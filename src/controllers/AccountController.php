<?php

require_once __DIR__ . '/../models/Account.php';

class AccountController {
    private Account $model;

    public function __construct() {
        $this->model = new Account();
    }

    public function handlePost(int $userId): void {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'create':
                $this->model->create($userId, $this->extractData());
                $this->flash('Account created successfully.', 'success');
                break;

            case 'update':
                $id = (int)($_POST['id'] ?? 0);
                if (!$this->model->find($id, $userId)) {
                    $this->flash('Account not found.', 'danger');
                    break;
                }
                $this->model->update($id, $userId, $this->extractData());
                $this->flash('Account updated successfully.', 'success');
                break;

            case 'delete':
                $id = (int)($_POST['id'] ?? 0);
                $this->model->delete($id, $userId);
                $this->flash('Account deleted.', 'success');
                break;

            default:
                $this->flash('Unknown action.', 'danger');
        }

        $redirect = $_POST['redirect'] ?? '/cashtimeline/public/accounts';
        header('Location: ' . $redirect);
        exit;
    }

    public function getJson(int $id, int $userId): void {
        $acc = $this->model->find($id, $userId);
        header('Content-Type: application/json');
        echo json_encode($acc ?: []);
        exit;
    }

    private function extractData(): array {
        return [
            'name'            => trim($_POST['name'] ?? ''),
            'type'            => $_POST['type'] ?? 'checking',
            'currency'        => $_POST['currency'] ?? 'CAD',
            'initial_balance' => (float)($_POST['initial_balance'] ?? 0),
            'color'           => $_POST['color'] ?? '#6366f1',
            'sort_order'      => (int)($_POST['sort_order'] ?? 0),
            'exchange_rate'   => (float)($_POST['exchange_rate'] ?? 1.0),
        ];
    }

    public function handleReorder(int $userId): void {
        $ids = json_decode(file_get_contents('php://input'), true) ?? [];
        if (is_array($ids)) {
            $this->model->reorder($userId, $ids);
        }
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        exit;
    }

    private function flash(string $msg, string $type): void {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
    }
}
