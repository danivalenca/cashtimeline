<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';
require_once __DIR__ . '/../src/controllers/AccountController.php';
require_once __DIR__ . '/../src/models/Account.php';
require_once __DIR__ . '/../src/models/Category.php';

AuthController::requireAuth();
$userId = AuthController::userId();

// AJAX: get single account data
if (isset($_GET['action']) && $_GET['action'] === 'get' && isset($_GET['id'])) {
    $ctrl = new AccountController();
    $ctrl->getJson((int)$_GET['id'], $userId);
}

// AJAX: reorder accounts (drag-and-drop)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'reorder') {
    $ctrl = new AccountController();
    $ctrl->handleReorder($userId);
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ctrl = new AccountController();
    $ctrl->handlePost($userId);
}

$accModel = new Account();
$accounts = $accModel->allForUser($userId);

$pageTitle  = 'Accounts';
$activePage = 'accounts';
include __DIR__ . '/partials/_head.php';
?>

<div class="app-shell">
    <?php include __DIR__ . '/partials/_nav.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title">
                <i class="fa-solid fa-building-columns me-2" style="color:var(--accent);"></i>Accounts
            </div>
            <div class="topbar-actions">
                <button class="btn-accent btn-accent-sm" onclick="openAccountCreate()">
                    <i class="fa-solid fa-plus"></i> Add Account
                </button>
            </div>
        </div>

        <?php include __DIR__ . '/partials/_flash.php'; ?>

        <div class="page-body">
            <?php if (empty($accounts)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-building-columns d-block mb-3"></i>
                    <p style="font-size:16px;font-weight:600;color:var(--text-primary);">No accounts yet</p>
                    <p>Add your bank accounts to start tracking your finances.</p>
                    <button class="btn-accent mt-2" onclick="openAccountCreate()">
                        <i class="fa-solid fa-plus me-1"></i> Add your first account
                    </button>
                </div>
            <?php else: ?>
                <!-- Summary bar -->
                <div class="d-flex gap-3 mb-4 flex-wrap">
                    <?php
                    $totalNetWorth = 0;
                    foreach ($accounts as $acct) {
                        $totalNetWorth += $accModel->currentBalance($acct['id'], $userId) * (float)($acct['exchange_rate'] ?? 1.0);
                    }
                    ?>
                    <div class="stat-card" style="flex:1;min-width:200px;">
                        <div class="stat-icon" style="background:#ede9fe;color:#7c3aed;">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                        <div>
                            <div class="stat-label">Total Accounts</div>
                            <div class="stat-value"><?= count($accounts) ?></div>
                        </div>
                    </div>
                    <div class="stat-card" style="flex:1;min-width:200px;">
                        <div class="stat-icon" style="background:<?= $totalNetWorth >= 0 ? 'var(--income-bg)' : 'var(--expense-bg)' ?>;color:<?= $totalNetWorth >= 0 ? 'var(--income-color)' : 'var(--expense-color)' ?>;">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                        <div>
                            <div class="stat-label">Net Worth Today</div>
                            <div class="stat-value <?= $totalNetWorth >= 0 ? 'positive' : 'negative' ?>">
                                <?= $totalNetWorth < 0 ? '-' : '' ?>CA$<?= number_format(abs($totalNetWorth), 2) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account cards grid -->
                <div class="row g-3" id="accountsList">
                    <?php foreach ($accounts as $acct):
                        $balance = $accModel->currentBalance($acct['id'], $userId);
                    ?>
                    <div class="col-12 col-md-6 col-xl-4" data-id="<?= $acct['id'] ?>">
                        <div class="account-card" style="border-left-color:<?= htmlspecialchars($acct['color']) ?>;">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="drag-handle" title="Drag to reorder"><i class="fa-solid fa-grip-vertical"></i></div>
                                    <span class="account-card-name"><?= htmlspecialchars($acct['name']) ?></span>
                                </div>
                                <span class="account-type-badge"><?= htmlspecialchars($acct['type']) ?></span>
                            </div>
                            <div class="account-card-balance <?= $balance < 0 ? 'text-expense' : '' ?>">
                                <?= $balance < 0 ? '-' : '' ?><?= htmlspecialchars($acct['currency']) ?>$<?= number_format(abs($balance), 2) ?>
                            </div>
                            <div style="font-size:11.5px;color:var(--text-muted);">
                                Opening: <?= $acct['currency'] ?>$<?= number_format($acct['initial_balance'], 2) ?>
                            </div>
                            <div class="account-card-actions">
                                <button class="btn-ghost" style="padding:5px 12px;font-size:12px;border-radius:6px;"
                                        onclick="openAccountEdit(<?= $acct['id'] ?>)">
                                    <i class="fa-solid fa-pen me-1"></i> Edit
                                </button>
                                <a href="/cashtimeline/public/transactions?account_id=<?= $acct['id'] ?>"
                                   class="btn-ghost" style="padding:5px 12px;font-size:12px;border-radius:6px;">
                                    <i class="fa-solid fa-list me-1"></i> Transactions
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/partials/offcanvas/_account.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="/cashtimeline/public/assets/js/app.js"></script>
<script>
function openAccountCreate() {
    document.getElementById('accountOffcanvasTitle').textContent = 'Add Account';
    document.getElementById('accountAction').value  = 'create';
    document.getElementById('accountId').value      = '';
    document.getElementById('accountName').value    = '';
    document.getElementById('accountType').value    = 'checking';
    document.getElementById('accountCurrency').value = 'CAD';
    document.getElementById('accountBalance').value = '0';
    document.getElementById('accountColor').value   = '#6366f1';
    document.getElementById('accountSortOrder').value = '0';
    document.getElementById('accountExchangeRate').value = '1';
    document.getElementById('accountDeleteZone').classList.add('d-none');
    updateRateSection();
    new bootstrap.Offcanvas(document.getElementById('accountOffcanvas')).show();
}

function openAccountEdit(id) {
    fetch(`/cashtimeline/public/accounts?action=get&id=${id}`)
        .then(r => r.json())
        .then(acc => {
            if (!acc || !acc.id) return;
            document.getElementById('accountOffcanvasTitle').textContent = 'Edit Account';
            document.getElementById('accountAction').value    = 'update';
            document.getElementById('accountId').value        = acc.id;
            document.getElementById('accountName').value      = acc.name;
            document.getElementById('accountType').value      = acc.type;
            document.getElementById('accountCurrency').value  = acc.currency;
            document.getElementById('accountBalance').value   = acc.initial_balance;
            document.getElementById('accountColor').value     = acc.color;
            document.getElementById('accountSortOrder').value = acc.sort_order;
            document.getElementById('accountExchangeRate').value = acc.exchange_rate || 1;
            document.getElementById('accountDeleteZone').classList.remove('d-none');
            document.getElementById('accountDeleteId').value  = acc.id;
            updateRateSection();
            new bootstrap.Offcanvas(document.getElementById('accountOffcanvas')).show();
        });
}

document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('accountsList');
    if (list && typeof Sortable !== 'undefined') {
        Sortable.create(list, {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            onEnd: () => {
                const ids = Array.from(list.querySelectorAll('[data-id]')).map(el => el.dataset.id);
                fetch('/cashtimeline/public/accounts?action=reorder', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(ids)
                });
            }
        });
    }
});
</script>
</body>
</html>
