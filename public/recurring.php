<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';
require_once __DIR__ . '/../src/controllers/RecurringController.php';
require_once __DIR__ . '/../src/models/RecurringRule.php';
require_once __DIR__ . '/../src/models/Account.php';

AuthController::requireAuth();
$userId = AuthController::userId();

// AJAX: get rule
if (isset($_GET['action']) && $_GET['action'] === 'get' && isset($_GET['id'])) {
    $ctrl = new RecurringController();
    $ctrl->getJson((int)$_GET['id'], $userId);
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ctrl = new RecurringController();
    $ctrl->handlePost($userId);
}

$ruleModel  = new RecurringRule();
$accModel   = new Account();

$rules      = $ruleModel->allForUser($userId);
$accounts   = $accModel->allForUser($userId);

$pageTitle  = 'Recurring Rules';
$activePage = 'recurring';
include __DIR__ . '/partials/_head.php';

function nextOccurrence(array $rule): string {
    $today = date('Y-m-d');
    switch ($rule['frequency']) {
        case 'monthly':
            $day = $rule['day_of_month'] ?: (int)date('d', strtotime($rule['start_date']));
            $candidate = date('Y-m-') . str_pad($day, 2, '0', STR_PAD_LEFT);
            if ($candidate < $today) {
                $candidate = date('Y-m-', strtotime('+1 month')) . str_pad($day, 2, '0', STR_PAD_LEFT);
            }
            return date('M j, Y', strtotime($candidate));
        case 'biweekly':
            $startTs = strtotime($rule['start_date']);
            $todayTs = strtotime($today);
            $diff    = (int)floor(($todayTs - $startTs) / 86400);
            $rem     = $diff % 14;
            $daysUntil = $rem === 0 ? 0 : (14 - $rem);
            return date('M j, Y', strtotime('+' . $daysUntil . ' days'));
        case 'weekly':
            return 'Weekly';
        case 'daily':
            return 'Daily';
        case 'yearly':
            return date('M j', strtotime($rule['start_date'])) . ' (yearly)';
        default:
            return '—';
    }
}
?>

<div class="app-shell">
    <?php include __DIR__ . '/partials/_nav.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title">
                <i class="fa-solid fa-rotate me-2" style="color:var(--accent);"></i>Recurring Rules
            </div>
            <div class="topbar-actions">
                <button class="btn-accent btn-accent-sm" onclick="openRecurringCreate()">
                    <i class="fa-solid fa-plus"></i> Add Rule
                </button>
            </div>
        </div>

        <?php include __DIR__ . '/partials/_flash.php'; ?>

        <div class="page-body">
            <div class="ct-card">
                <div class="ct-card-header">
                    <h6><i class="fa-solid fa-rotate me-2" style="color:var(--accent);"></i>
                        <?= count($rules) ?> rule<?= count($rules) !== 1 ? 's' : '' ?>
                    </h6>
                </div>

                <?php if (empty($rules)): ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-rotate"></i>
                        <p>No recurring rules yet.<br>
                           Add rules for regular transactions like rent, salary, or subscriptions.</p>
                        <button class="btn-accent mt-2" onclick="openRecurringCreate()">
                            <i class="fa-solid fa-plus me-1"></i> Add first rule
                        </button>
                    </div>
                <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="ct-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Account</th>
                                <th>Type</th>
                                <th>Frequency</th>
                                <th>Next</th>
                                <th>Start</th>
                                <th>End</th>
                                <th style="text-align:right;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rules as $rule): ?>
                            <tr onclick="openRecurringEdit(<?= $rule['id'] ?>)">
                                <td style="font-weight:600;"><?= htmlspecialchars($rule['description']) ?></td>
                                <td style="color:var(--text-muted);"><?= htmlspecialchars($rule['account_name'] ?? '—') ?></td>
                                <td>
                                    <span class="badge-type <?= $rule['type'] === 'income' ? 'badge-income' : 'badge-expense' ?>">
                                        <?= ucfirst($rule['type']) ?>
                                    </span>
                                </td>
                                <td><span class="freq-badge"><?= ucfirst($rule['frequency']) ?></span></td>
                                <td style="font-size:12.5px;color:var(--text-muted);"><?= nextOccurrence($rule) ?></td>
                                <td style="font-size:12.5px;color:var(--text-muted);"><?= date('M j, Y', strtotime($rule['start_date'])) ?></td>
                                <td style="font-size:12.5px;color:var(--text-muted);">
                                    <?= $rule['end_date'] ? date('M j, Y', strtotime($rule['end_date'])) : '<span style="color:#d1d5db;">∞</span>' ?>
                                </td>
                                <td style="text-align:right;font-weight:700;font-variant-numeric:tabular-nums;white-space:nowrap;"
                                    class="<?= $rule['type'] === 'income' ? 'text-income' : 'text-expense' ?>">
                                    <?= $rule['type'] === 'income' ? '+' : '-' ?>CA$<?= number_format($rule['amount'], 2) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/partials/offcanvas/_recurring.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="/cashtimeline/public/assets/js/app.js"></script>
<script>
function openRecurringCreate() {
    document.getElementById('recurringOffcanvasTitle').textContent = 'Add Recurring Rule';
    document.getElementById('recurringAction').value       = 'create';
    document.getElementById('recurringId').value           = '';
    document.getElementById('recurringDescription').value  = '';
    document.getElementById('recurringAmount').value       = '';
    document.getElementById('recurringAccount').value      = '';
    document.getElementById('recurringFrequency').value    = 'monthly';
    document.getElementById('recurringDayOfMonth').value   = '';
    document.getElementById('recurringStartDate').value    = '<?= date('Y-m-d') ?>';
    document.getElementById('recurringEndDate').value      = '';
    document.getElementById('recTypeExpense').checked      = true;
    document.getElementById('recurringDeleteZone').classList.add('d-none');
    document.getElementById('dayOfMonthGroup').style.display = '';
    new bootstrap.Offcanvas(document.getElementById('recurringOffcanvas')).show();
}

function openRecurringEdit(id) {
    fetch(`/cashtimeline/public/recurring?action=get&id=${id}`)
        .then(r => r.json())
        .then(rule => {
            if (!rule || !rule.id) return;
            document.getElementById('recurringOffcanvasTitle').textContent = 'Edit Recurring Rule';
            document.getElementById('recurringAction').value       = 'update';
            document.getElementById('recurringId').value           = rule.id;
            document.getElementById('recurringDescription').value  = rule.description;
            document.getElementById('recurringAmount').value       = rule.amount;
            document.getElementById('recurringAccount').value      = rule.account_id;
            document.getElementById('recurringFrequency').value    = rule.frequency;
            document.getElementById('recurringDayOfMonth').value   = rule.day_of_month || '';
            document.getElementById('recurringStartDate').value    = rule.start_date;
            document.getElementById('recurringEndDate').value      = rule.end_date || '';
            document.getElementById('recurringNotifyBefore').value = rule.notify_before_days || '0';
            document.getElementById(rule.type === 'income' ? 'recTypeIncome' : 'recTypeExpense').checked = true;
            document.getElementById('recurringDeleteZone').classList.remove('d-none');
            document.getElementById('recurringDeleteId').value     = rule.id;
            toggleDayOfMonth(rule.frequency);
            new bootstrap.Offcanvas(document.getElementById('recurringOffcanvas')).show();
        });
}

<?php if (!empty($_GET['edit'])): ?>
document.addEventListener('DOMContentLoaded', () => openRecurringEdit(<?= (int)$_GET['edit'] ?>));
<?php endif; ?>
</script>
</body>
</html>
