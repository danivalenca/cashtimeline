<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';
require_once __DIR__ . '/../src/controllers/TransactionController.php';
require_once __DIR__ . '/../src/models/Transaction.php';
require_once __DIR__ . '/../src/models/Account.php';

AuthController::requireAuth();
$userId = AuthController::userId();

// AJAX: get a single transaction
if (isset($_GET['action']) && $_GET['action'] === 'get' && isset($_GET['id'])) {
    $ctrl = new TransactionController();
    $ctrl->getJson((int)$_GET['id'], $userId);
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ctrl = new TransactionController();
    $ctrl->handlePost($userId);
}

$txnModel = new Transaction();
$accModel = new Account();

// Filters from query string
$filters = [
    'account_id' => (int)($_GET['account_id'] ?? 0) ?: null,
    'type'       => $_GET['type'] ?? null,
    'date_from'  => $_GET['date_from'] ?? null,
    'date_to'    => $_GET['date_to']   ?? null,
];

$transactions = $txnModel->allForUser($userId, array_filter($filters));
$accounts     = $accModel->allForUser($userId);
$categories   = []; // categories removed

// Running balance (most recent first, so reverse)
$pageTitle  = 'Transactions';
$activePage = 'transactions';
include __DIR__ . '/partials/_head.php';
?>

<div class="app-shell">
    <?php include __DIR__ . '/partials/_nav.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title">
                <i class="fa-solid fa-arrow-right-arrow-left me-2" style="color:var(--accent);"></i>Transactions
            </div>
            <div class="topbar-actions">
                <button class="btn-accent btn-accent-sm" onclick="openTxnCreate()">
                    <i class="fa-solid fa-plus"></i> Add Transaction
                </button>
            </div>
        </div>

        <?php include __DIR__ . '/partials/_flash.php'; ?>

        <div class="page-body">
            <!-- Filter bar -->
            <div class="ct-card mb-4">
                <div class="ct-card-body" style="padding:16px 20px;">
                    <form method="GET" class="filter-bar">
                        <div>
                            <label class="form-label-sm">Account</label>
                            <select name="account_id" class="form-select-ct">
                                <option value="">All accounts</option>
                                <?php foreach ($accounts as $a): ?>
                                    <option value="<?= $a['id'] ?>" <?= ($filters['account_id'] == $a['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($a['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label-sm">Type</label>
                            <select name="type" class="form-select-ct">
                                <option value="">All types</option>
                                <option value="income"  <?= $filters['type'] === 'income'  ? 'selected' : '' ?>>Income</option>
                                <option value="expense" <?= $filters['type'] === 'expense' ? 'selected' : '' ?>>Expense</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label-sm">From</label>
                            <input type="date" name="date_from" class="form-control-ct"
                                   value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                        </div>
                        <div>
                            <label class="form-label-sm">To</label>
                            <input type="date" name="date_to" class="form-control-ct"
                                   value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
                        </div>
                        <div style="display:flex;align-items:flex-end;gap:6px;">
                            <button type="submit" class="btn-accent btn-accent-sm">
                                <i class="fa-solid fa-filter me-1"></i> Apply
                            </button>
                            <a href="/cashtimeline/public/transactions" class="btn-ghost" style="padding:5px 12px;font-size:12px;">Clear</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Transactions table -->
            <div class="ct-card">
                <div class="ct-card-header">
                    <h6><i class="fa-solid fa-list me-2" style="color:var(--accent);"></i>
                        <?= count($transactions) ?> transaction<?= count($transactions) !== 1 ? 's' : '' ?>
                    </h6>
                </div>

                <?php if (empty($transactions)): ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-arrow-right-arrow-left"></i>
                        <p>No transactions found. <button class="btn-ghost" onclick="openTxnCreate()" style="font-size:13px;">Add one</button></p>
                    </div>
                <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="ct-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Account</th>
                                <th>Type</th>
                                <th style="text-align:right;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $txn): ?>
                            <tr onclick="openTxnEdit(<?= $txn['id'] ?>)">
                                <td style="white-space:nowrap;color:var(--text-muted);font-size:12.5px;">
                                    <?= date('M j, Y', strtotime($txn['transaction_date'])) ?>
                                </td>
                                <td style="font-weight:500;"><?= htmlspecialchars($txn['description']) ?></td>
                                <td style="color:var(--text-muted);"><?= htmlspecialchars($txn['account_name'] ?? '—') ?></td>
                                <td>
                                    <span class="badge-type <?= $txn['type'] === 'income' ? 'badge-income' : 'badge-expense' ?>">
                                        <?= ucfirst($txn['type']) ?>
                                    </span>
                                </td>
                                <td style="text-align:right;font-weight:600;font-variant-numeric:tabular-nums;white-space:nowrap;"
                                    class="<?= $txn['type'] === 'income' ? 'text-income' : 'text-expense' ?>">
                                    <?= $txn['type'] === 'income' ? '+' : '-' ?><?= htmlspecialchars($txn['currency'] ?? 'CA') ?>$<?= number_format($txn['amount'], 2) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div><!-- end page-body -->
    </div><!-- end main-content -->
</div>

<?php include __DIR__ . '/partials/offcanvas/_transaction.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="/cashtimeline/public/assets/js/app.js"></script>
<script>
function openTxnCreate() {
    resetTxnOffcanvas();
    document.getElementById('txnOffcanvasTitle').textContent = 'Add Transaction';
    document.getElementById('txnAction').value = 'create';
    document.getElementById('txnId').value     = '';
    document.getElementById('txnDate').value   = '<?= date('Y-m-d') ?>';
    document.getElementById('txnDeleteZone').classList.add('d-none');
    new bootstrap.Offcanvas(document.getElementById('txnOffcanvas')).show();
}

function openTxnEdit(id) {
    fetch(`/cashtimeline/public/transactions?action=get&id=${id}`)
        .then(r => r.json())
        .then(txn => {
            if (!txn || !txn.id) return;
            resetTxnOffcanvas();
            document.getElementById('txnOffcanvasTitle').textContent = 'Edit Transaction';
            document.getElementById('txnAction').value       = 'update';
            document.getElementById('txnId').value           = txn.id;
            document.getElementById('txnDate').value         = txn.transaction_date;
            document.getElementById('txnAccount').value      = txn.account_id;
            document.getElementById('txnDescription').value  = txn.description;
            document.getElementById('txnAmount').value       = txn.amount;
            document.getElementById('txnNotify').checked     = !!txn.notify_on_date;
            document.querySelectorAll('input[name=type]').forEach(r => r.checked = r.value === txn.type);
            document.getElementById('txnDeleteZone').classList.remove('d-none');
            document.getElementById('txnDeleteId').value        = txn.id;
            document.getElementById('txnProcessId').value       = txn.id;
            new bootstrap.Offcanvas(document.getElementById('txnOffcanvas')).show();
        });
}

// Process recurring occurrence
function processRecurringOccurrence() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/cashtimeline/public/transactions';
    
    const fields = {
        action: 'process_recurring',
        account_id: document.getElementById('txnAccount').value,
        description: document.getElementById('txnDescription').value,
        amount: document.getElementById('txnAmount').value,
        transaction_date: document.getElementById('txnDate').value,
        type: document.querySelector('input[name=type]:checked').value,
        redirect: '<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/cashtimeline/public/transactions') ?>'
    };
    
    for (const [name, value] of Object.entries(fields)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
    }
    
    if (confirm('Process this recurring occurrence and update account balance?')) {
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
</body>
</html>
