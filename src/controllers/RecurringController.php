<?php

require_once __DIR__ . '/../models/RecurringRule.php';

class RecurringController {
    private RecurringRule $model;

    public function __construct() {
        $this->model = new RecurringRule();
    }

    public function handlePost(int $userId): void {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'create':
                $this->model->create($userId, $this->extractData());
                $this->flash('Recurring rule created.', 'success');
                break;

            case 'update':
                $id = (int)($_POST['id'] ?? 0);
                if (!$this->model->find($id, $userId)) {
                    $this->flash('Rule not found.', 'danger');
                    break;
                }
                $this->model->update($id, $userId, $this->extractData());
                $this->flash('Recurring rule updated.', 'success');
                break;

            case 'delete':
                $id = (int)($_POST['id'] ?? 0);
                $this->model->delete($id, $userId);
                $this->flash('Recurring rule deleted.', 'success');
                break;

            default:
                $this->flash('Unknown action.', 'danger');
        }

        $redirect = $_POST['redirect'] ?? '/cashtimeline/public/recurring';
        header('Location: ' . $redirect);
        exit;
    }

    public function getJson(int $id, int $userId): void {
        $rule = $this->model->find($id, $userId);
        header('Content-Type: application/json');
        echo json_encode($rule ?: []);
        exit;
    }

    private function extractData(): array {
        return [
            'account_id'   => (int)($_POST['account_id'] ?? 0),
            'type'         => $_POST['type'] ?? 'expense',
            'amount'       => abs((float)($_POST['amount'] ?? 0)),
            'description'  => trim($_POST['description'] ?? ''),
            'frequency'    => $_POST['frequency'] ?? 'monthly',
            'day_of_month' => (int)($_POST['day_of_month'] ?? 0) ?: null,
            'start_date'   => $_POST['start_date'] ?? date('Y-m-d'),
            'end_date'     => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
            'notify_before_days' => (int)($_POST['notify_before_days'] ?? 0),
        ];
    }

    private function flash(string $msg, string $type): void {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
    }
}
