<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';
require_once __DIR__ . '/../src/controllers/TimelineController.php';
require_once __DIR__ . '/../src/controllers/TransactionController.php';
require_once __DIR__ . '/../src/controllers/AccountController.php';
require_once __DIR__ . '/../src/models/Account.php';

AuthController::requireAuth();
$userId = AuthController::userId();

// AJAX: reorder accounts (sidebar drag-and-drop)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'reorder') {
    $ids = json_decode(file_get_contents('php://input'), true) ?? [];
    (new Account())->reorder($userId, $ids);
    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit;
}

// AJAX: get single transaction
if (isset($_GET['action']) && $_GET['action'] === 'get_txn' && isset($_GET['id'])) {
    require_once __DIR__ . '/../src/models/Transaction.php';
    $txnModel = new Transaction();
    $txn = $txnModel->find((int)$_GET['id'], $userId);
    header('Content-Type: application/json');
    echo json_encode($txn ?: []);
    exit;
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ctrl = new TransactionController();
    $ctrl->handlePost($userId);
}

// View mode: timeline (default) or calendar
$view = $_GET['view'] ?? 'timeline';
if (!in_array($view, ['timeline', 'calendar'])) $view = 'timeline';

// Month navigation
$today      = new DateTime();
$startYear  = (int)($_GET['y'] ?? $today->format('Y'));
$startMonth = (int)($_GET['m'] ?? $today->format('n'));
if ($startMonth < 1)  { $startMonth = 12; $startYear--; }
if ($startMonth > 12) { $startMonth = 1;  $startYear++; }

$timelineCtrl = new TimelineController();
if ($view === 'calendar') {
    $todayY = (int)$today->format('Y');
    $todayM = (int)$today->format('n');
    $monthsAhead = ($startYear - $todayY) * 12 + ($startMonth - $todayM);
    if ($monthsAhead > 0) {
        // Build from current month through target so carry-forward is correct
        $allCols = $timelineCtrl->buildTimeline($userId, $todayY, $todayM, $monthsAhead + 1);
        $columns = [array_pop($allCols)];
    } else {
        $columns = $timelineCtrl->buildTimeline($userId, $startYear, $startMonth, 1);
    }
} else {
    $columns = $timelineCtrl->buildTimeline($userId, $startYear, $startMonth, 6);
}

$accModel = new Account();
$accounts = $accModel->allForUser($userId);
$categories = []; // categories removed from UI

// Calendar: group merged rows by day number
$txnByDay = [];
if ($view === 'calendar' && !empty($columns[0]['rows'])) {
    foreach ($columns[0]['rows'] as $row) {
        $d = (int)date('d', strtotime($row['transaction_date']));
        $txnByDay[$d][] = $row;
    }
}

// Net worth today
$netWorthNow = $accModel->netWorthAtDate($userId, date('Y-m-d'));
$alerts      = $accModel->upcomingExpenseAlerts($userId);

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
include __DIR__ . '/partials/_head.php';

function formatMoney(float $amount, string $currency = 'CAD'): string {
    return $currency . '$' . number_format(abs($amount), 2);
}

// Prev / next month (preserve view)
$prevM = $startMonth - 1; $prevY = $startYear;
if ($prevM < 1)  { $prevM = 12; $prevY--; }
$nextM = $startMonth + 1; $nextY = $startYear;
if ($nextM > 12) { $nextM = 1;  $nextY++; }
?>

<div class="app-shell">
    <?php include __DIR__ . '/partials/_nav.php'; ?>

    <div class="main-content">
        <!-- Top bar -->
        <div class="topbar">
            <div class="topbar-title">
                <i class="fa-solid <?= $view === 'calendar' ? 'fa-calendar-days' : 'fa-chart-gantt' ?> me-2" style="color:var(--accent);"></i>
                <?= $view === 'calendar' ? 'Calendar' : 'Timeline' ?>
            </div>
            <div class="topbar-actions">
                <!-- View toggle -->
                <div class="view-toggle">
                    <a href="?view=timeline&y=<?= $startYear ?>&m=<?= $startMonth ?>"
                       class="view-toggle-btn <?= $view === 'timeline' ? 'active' : '' ?>">
                        <i class="fa-solid fa-chart-gantt me-1"></i>Timeline
                    </a>
                    <a href="?view=calendar&y=<?= $startYear ?>&m=<?= $startMonth ?>"
                       class="view-toggle-btn <?= $view === 'calendar' ? 'active' : '' ?>">
                        <i class="fa-solid fa-calendar-days me-1"></i>Calendar
                    </a>
                </div>
                <div style="width:1px;height:20px;background:var(--border);"></div>
                <!-- Month navigation -->
                <a href="?view=<?= $view ?>&y=<?= $prevY ?>&m=<?= $prevM ?>" class="btn-ghost" title="Previous">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
                <span style="font-size:13px;color:var(--text-muted);padding:0 6px;white-space:nowrap;">
                    <?php if ($view === 'timeline'): ?>
                        <?= date('M Y', mktime(0,0,0,$startMonth,1,$startYear)) ?>
                        &rarr;
                        <?php
                            $em = $startMonth + 5; $ey = $startYear;
                            while ($em > 12) { $em -= 12; $ey++; }
                            echo date('M Y', mktime(0,0,0,$em,1,$ey));
                        ?>
                    <?php else: ?>
                        <?= date('F Y', mktime(0,0,0,$startMonth,1,$startYear)) ?>
                    <?php endif; ?>
                </span>
                <a href="?view=<?= $view ?>&y=<?= $nextY ?>&m=<?= $nextM ?>" class="btn-ghost" title="Next">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
                <div style="width:1px;height:20px;background:var(--border);"></div>
                <button class="btn-accent btn-accent-sm" onclick="openTxnCreate()">
                    <i class="fa-solid fa-plus"></i> Add Transaction
                </button>
                <button class="btn-accent btn-accent-sm" onclick="openRecurringCreate()" style="background:var(--accent-2,#7c3aed);">
                    <i class="fa-solid fa-rotate"></i> Add Recurring
                </button>
            </div>
        </div>

        <?php include __DIR__ . '/partials/_flash.php'; ?>

        <?php if (!empty($alerts)): ?>
        <div class="balance-alerts-bar">
            <?php foreach ($alerts as $al): ?>
            <div class="balance-alert">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div style="flex:1;min-width:0;">
                    <strong><?= htmlspecialchars($al['account']) ?></strong>:
                    upcoming expense <strong><?= htmlspecialchars($al['description']) ?></strong>
                    on <?= date('M j', strtotime($al['date'])) ?>
                    costs <?= $al['currency'] ?>$<?= number_format($al['amount'], 2) ?>
                    but balance is only <?= $al['currency'] ?>$<?= number_format(max(0, $al['balance']), 2) ?>.
                    <strong class="balance-alert-deficit">Shortfall: <?= $al['currency'] ?>$<?= number_format($al['deficit'], 2) ?></strong>
                </div>
                <button class="balance-alert-close" onclick="this.closest('.balance-alert').remove()">&times;</button>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="page-body" style="padding:0;display:flex;flex-direction:column;height:calc(100vh - 60px);">
            <div style="display:flex;flex:1;overflow:hidden;min-height:0;">

                <!-- Accounts sidebar -->
                <div class="accounts-panel ct-card" style="border-radius:0;border-top:none;border-bottom:none;border-left:none;overflow:hidden;flex-shrink:0;">
                    <div class="ct-card-header">
                        <h6><i class="fa-solid fa-building-columns me-2" style="color:var(--accent);"></i>Accounts</h6>
                        <button class="btn-accent btn-accent-sm" style="padding:3px 10px;font-size:11px;" onclick="openAccountCreate()" title="Add account">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                    <div class="ct-card-body" style="padding:12px 16px;flex:1;overflow-y:auto;display:flex;flex-direction:column;">
                        <?php if (empty($accounts)): ?>
                            <div class="empty-state" style="padding:30px 10px;">
                                <i class="fa-solid fa-building-columns"></i>
                                <p>No accounts yet.<br><a href="/cashtimeline/public/accounts" style="color:var(--accent);">Add one</a></p>
                            </div>
                        <?php else: ?>
                            <div id="sidebarAccountList" style="flex:1;">
                            <?php foreach ($accounts as $acct):
                                $bal = $accModel->currentBalance($acct['id'], $userId);
                            ?>
                            <div class="account-item" data-id="<?= $acct['id'] ?>" style="cursor:pointer;" onclick="openAccountEdit(<?= $acct['id'] ?>)" title="Click to edit">
                                <div class="d-flex align-items-center gap-2" style="min-width:0;">
                                    <div class="drag-handle-sm" onclick="event.stopPropagation()" title="Drag to reorder">
                                        <i class="fa-solid fa-grip-vertical" style="font-size:10px;color:var(--text-muted);opacity:.5;"></i>
                                    </div>
                                    <div class="account-dot" style="background:<?= htmlspecialchars($acct['color']) ?>;"></div>
                                    <span class="account-name text-truncate"><?= htmlspecialchars($acct['name']) ?></span>
                                </div>
                                <span class="account-balance <?= $bal < 0 ? 'text-expense' : '' ?>" style="white-space:nowrap;margin-left:8px;">
                                    <?= $bal < 0 ? '-' : '' ?><?= formatMoney(abs($bal), $acct['currency']) ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- Net worth pinned at bottom -->
                    <div class="month-footer" style="border-radius:0;">
                        <span class="footer-label">Net Worth</span>
                        <span class="footer-value <?= $netWorthNow < 0 ? 'text-expense' : 'text-income' ?>">
                            <?= $netWorthNow < 0 ? '-' : '' ?>CA$<?= number_format(abs($netWorthNow), 2) ?>
                        </span>
                    </div>
                </div>

                <!-- Main panel: Timeline or Calendar -->
                <?php if ($view === 'timeline'): ?>
                <div class="timeline-wrapper flex-grow-1" style="overflow:auto;height:100%;">
                    <?php if (empty($accounts)): ?>
                        <div class="empty-state" style="padding:80px 40px;">
                            <i class="fa-solid fa-chart-gantt"></i>
                            <p style="font-size:15px;">Start by <a href="/cashtimeline/public/accounts" style="color:var(--accent);">adding your bank accounts</a>.</p>
                        </div>
                    <?php else: ?>
                    <div class="timeline-grid">
                        <?php
                        $todayStr = date('Y-m-d');
                        $todayDt  = new DateTime($todayStr);

                        foreach ($columns as $col):
                            $colDt  = new DateTime(sprintf('%04d-%02d-01', $col['year'], $col['month']));
                            $isFuture = $colDt > $todayDt;
                        ?>
                        <div class="month-col <?= $isFuture ? 'future' : '' ?>">
                            <!-- Month header -->
                            <div class="month-header">
                                <h6><?= htmlspecialchars($col['label']) ?></h6>
                                <?php if ($isFuture): ?>
                                    <span style="font-size:10px;color:var(--text-muted);background:#f3f4f6;padding:2px 7px;border-radius:10px;">Projected</span>
                                <?php endif; ?>
                            </div>

                            <!-- Transaction rows -->
                            <div style="flex:1;">
                            <?php if (empty($col['rows']) && $col['year'] === (int)date('Y') && $col['month'] === (int)date('n')): ?>
                                <!-- Today marker when no transactions -->
                                <div class="today-marker">
                                    <div class="today-marker-line"></div>
                                    <span class="today-marker-label"><i class="fa-solid fa-calendar-day"></i> Today - <?= date('M j') ?></span>
                                    <div class="today-marker-line"></div>
                                </div>
                            <?php elseif (empty($col['rows'])): ?>
                                <div style="text-align:center;padding:30px 16px;color:var(--text-muted);font-size:12px;">
                                    No transactions
                                </div>
                            <?php else: ?>
                                <?php 
                                $todayMarkerShown = false;
                                foreach ($col['rows'] as $idx => $row):
                                    // Show "Today" marker in current month between past and future transactions
                                    if (!$todayMarkerShown && $col['year'] === (int)date('Y') && $col['month'] === (int)date('n')) {
                                        if ($row['transaction_date'] > $todayStr) {
                                            $todayMarkerShown = true;
                                            echo '<div class="today-marker">';
                                            echo '<div class="today-marker-line"></div>';
                                            echo '<span class="today-marker-label"><i class="fa-solid fa-calendar-day"></i> Today - ' . date('M j') . '</span>';
                                            echo '<div class="today-marker-line"></div>';
                                            echo '</div>';
                                        }
                                    }
                                    $isRecurring = !empty($row['is_recurring']);
                                    $sign        = $row['type'] === 'income' ? '+' : '-';
                                    $amtClass    = $row['type'] === 'income' ? 'income' : 'expense';
                                    $dayNum      = date('d', strtotime($row['transaction_date']));
                                    $isToday     = $row['transaction_date'] === $todayStr;
                                    $isProcessed = !empty($row['processed']);
                                ?>
                                <div class="txn-row <?= $isRecurring ? 'is-recurring' : '' ?> <?= $isToday ? 'is-today' : '' ?> <?= $isProcessed ? 'is-processed' : '' ?>"
                                     <?php if (!$isRecurring): ?>
                                         onclick="openTxnEdit(<?= $row['id'] ?>)"
                                         title="Edit transaction"
                                     <?php else: ?>
                                         onclick="openRecurringOccurrence(<?= (int)$row['account_id'] ?>, <?= htmlspecialchars(json_encode($row['description'])) ?>, '<?= $row['transaction_date'] ?>', <?= (float)$row['amount'] ?>, '<?= $row['type'] ?>', <?= (int)ltrim((string)$row['id'], 'r') ?>)"
                                         title="Click to record this occurrence"
                                     <?php endif; ?>>
                                    <span class="txn-date"><?= $dayNum ?></span>
                                    <span class="txn-desc">
                                        <?php if ($isRecurring): ?>
                                            <span class="recurring-badge" title="Recurring"><i class="fa-solid fa-rotate" style="font-size:7px;"></i></span>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($row['description']) ?>
                                    </span>
                                    <span class="txn-amount <?= $amtClass ?>">
                                        <?= $sign ?>CA$<?= number_format($row['amount'], 2) ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                                <?php 
                                // Show "Today" marker at end if all transactions are in the past
                                if (!$todayMarkerShown && $col['year'] === (int)date('Y') && $col['month'] === (int)date('n')) {
                                    echo '<div class="today-marker">';
                                    echo '<div class="today-marker-line"></div>';
                                    echo '<span class="today-marker-label"><i class="fa-solid fa-calendar-day"></i> Today - ' . date('M j') . '</span>';
                                    echo '<div class="today-marker-line"></div>';
                                    echo '</div>';
                                }
                                ?>
                            <?php endif; ?>
                            </div>

                            <!-- Month footer: net worth -->
                            <div class="month-footer">
                                <span class="footer-label">End balance</span>
                                <span class="footer-value <?= $col['net_worth'] >= 0 ? 'text-income' : 'text-expense' ?>">
                                    <?= $col['net_worth'] < 0 ? '-' : '' ?>CA$<?= number_format(abs($col['net_worth']), 2) ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <?php else: /* Calendar view */ ?>
                <div class="flex-grow-1" style="display:flex;flex-direction:column;overflow:hidden;padding:24px;min-height:0;">
                    <?php if (empty($accounts)): ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-calendar-days"></i>
                            <p>Start by <a href="/cashtimeline/public/accounts" style="color:var(--accent);">adding your bank accounts</a>.</p>
                        </div>
                    <?php else:
                        $todayStr2    = date('Y-m-d');
                        $firstDow     = (int)date('w', mktime(0,0,0,$startMonth,1,$startYear)); // 0=Sun
                        $daysInMonth  = (int)date('t', mktime(0,0,0,$startMonth,1,$startYear));
                        $calNetWorth  = $columns[0]['net_worth'] ?? 0;
                        $numWeekRows  = (int)ceil(($firstDow + $daysInMonth) / 7);
                    ?>
                    <!-- Calendar header -->
                    <div class="cal-month-header">
                        <span class="cal-month-label"><?= date('F Y', mktime(0,0,0,$startMonth,1,$startYear)) ?></span>
                        <span class="cal-net-worth <?= $calNetWorth >= 0 ? 'text-income' : 'text-expense' ?>">
                            End balance: <?= $calNetWorth < 0 ? '-' : '' ?>CA$<?= number_format(abs($calNetWorth), 2) ?>
                        </span>
                    </div>

                    <!-- Calendar grid -->
                    <div class="cal-grid" style="flex:1;min-height:0;grid-template-rows:auto repeat(<?= $numWeekRows ?>, 1fr);">
                        <?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dow): ?>
                            <div class="cal-dow"><?= $dow ?></div>
                        <?php endforeach; ?>

                        <?php
                        $cellIdx = 0;
                        // Leading empty cells
                        for ($i = 0; $i < $firstDow; $i++) {
                            echo '<div class="cal-cell empty"></div>';
                            $cellIdx++;
                        }
                        // Day cells
                        for ($d = 1; $d <= $daysInMonth; $d++) {
                            $dateStr    = sprintf('%04d-%02d-%02d', $startYear, $startMonth, $d);
                            $isToday    = $dateStr === $todayStr2;
                            $isFutureD  = $dateStr > $todayStr2;
                            $cls        = 'cal-cell' . ($isToday ? ' today' : '') . ($isFutureD ? ' future-day' : '');
                            echo '<div class="' . $cls . '">';
                            echo '<div class="cal-day-num' . ($isToday ? ' today-num' : '') . '">' . $d . '</div>';
                            if (!empty($txnByDay[$d])) {
                                foreach ($txnByDay[$d] as $row) {
                                    $isRec   = !empty($row['is_recurring']);
                                    $isProc  = !empty($row['processed']);
                                    $sign    = $row['type'] === 'income' ? '+' : '-';
                                    $aCls    = $row['type'] === 'income' ? 'income' : 'expense';
                                    if ($isProc) $aCls .= ' processed';
                                    $ruleId  = (int)ltrim((string)$row['id'], 'r');
                                    $onclick = $isRec
                                        ? 'onclick="openRecurringOccurrence(' . (int)$row['account_id'] . ', ' . htmlspecialchars(json_encode($row['description']), ENT_QUOTES) . ', \'' . $row['transaction_date'] . '\', ' . (float)$row['amount'] . ', \'' . $row['type'] . '\', ' . $ruleId . ')"'
                                        : 'onclick="openTxnEdit(' . $row['id'] . ')"';
                                    echo '<div class="cal-txn ' . $aCls . '" ' . $onclick . ' title="' . htmlspecialchars($row['description']) . '">';
                                    if ($isProc) echo '<i class="fa-solid fa-check" style="font-size:8px;opacity:.6;flex-shrink:0;color:#22c55e;"></i>';
                                    else if ($isRec) echo '<i class="fa-solid fa-rotate" style="font-size:8px;opacity:.6;flex-shrink:0;"></i>';
                                    echo '<span class="cal-txn-desc">' . htmlspecialchars($row['description']) . '</span>';
                                    echo '<span class="cal-txn-amt">' . $sign . 'CA$' . number_format($row['amount'], 2) . '</span>';
                                    echo '</div>';
                                }
                            }
                            echo '</div>';
                            $cellIdx++;
                        }
                        // Trailing empty cells
                        while ($cellIdx % 7 !== 0) {
                            echo '<div class="cal-cell empty"></div>';
                            $cellIdx++;
                        }
                        ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </div>
        </div><!-- end page-body -->
    </div><!-- end main-content -->
</div><!-- end app-shell -->

<?php include __DIR__ . '/partials/offcanvas/_transaction.php'; ?>
<?php include __DIR__ . '/partials/offcanvas/_account.php'; ?>
<?php include __DIR__ . '/partials/offcanvas/_recurring.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="/cashtimeline/public/assets/js/app.js"></script>
<script>
function openTxnCreate() {
    resetTxnOffcanvas();
    document.getElementById('txnOffcanvasTitle').textContent = 'Add Transaction';
    document.getElementById('txnAction').value  = 'create';
    document.getElementById('txnId').value      = '';
    document.getElementById('txnDate').value    = '<?= date('Y-m-d') ?>';
    document.getElementById('txnDeleteZone').classList.add('d-none');
    new bootstrap.Offcanvas(document.getElementById('txnOffcanvas')).show();
}

function openRecurringOccurrence(accountId, description, date, amount, type, ruleId) {
    resetTxnOffcanvas();
    document.getElementById('txnOffcanvasTitle').textContent = 'Record Occurrence';
    document.getElementById('txnAction').value       = 'create';
    document.getElementById('txnId').value           = '';
    document.getElementById('txnDate').value         = date;
    document.getElementById('txnAccount').value      = accountId;
    document.getElementById('txnDescription').value  = description;
    document.getElementById('txnAmount').value       = amount;
    document.querySelectorAll('input[name=type]').forEach(r => r.checked = r.value === type);
    document.getElementById('txnDeleteZone').classList.add('d-none');
    
    // Show recurring zone with process button
    const recZone = document.getElementById('txnRecurringZone');
    if (recZone) {
        recZone.classList.remove('d-none');
        document.getElementById('txnEditRuleLink').href =
            '/cashtimeline/public/recurring?edit=' + ruleId;
        
        // Store data for process button
        recZone.dataset.accountId = accountId;
        recZone.dataset.description = description;
        recZone.dataset.date = date;
        recZone.dataset.amount = amount;
        recZone.dataset.type = type;
    }
    
    new bootstrap.Offcanvas(document.getElementById('txnOffcanvas')).show();
}

function openTxnEdit(id) {
    fetch(`/cashtimeline/public/dashboard?action=get_txn&id=${id}`)
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
            document.getElementById('txnDeleteId').value     = txn.id;
            document.getElementById('txnProcessId').value    = txn.id;
            new bootstrap.Offcanvas(document.getElementById('txnOffcanvas')).show();
        });
}

// ── Recurring offcanvas ──
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
        redirect: '<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/cashtimeline/public/dashboard') ?>'
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

// ── Account offcanvas ──
function openAccountCreate() {
    document.getElementById('accountOffcanvasTitle').textContent = 'Add Account';
    document.getElementById('accountAction').value   = 'create';
    document.getElementById('accountId').value       = '';
    document.getElementById('accountName').value     = '';
    document.getElementById('accountType').value     = 'checking';
    document.getElementById('accountCurrency').value = 'CAD';
    document.getElementById('accountBalance').value  = '0';
    document.getElementById('accountColor').value    = '#6366f1';
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

// ── Sidebar drag-and-drop ──
// Disable browser scroll restoration so the timeline always starts at the left
if ('scrollRestoration' in history) history.scrollRestoration = 'manual';

document.addEventListener('DOMContentLoaded', () => {
    const sidebarList = document.getElementById('sidebarAccountList');
    if (sidebarList && typeof Sortable !== 'undefined') {
        Sortable.create(sidebarList, {
            animation: 150,
            handle: '.drag-handle-sm',
            ghostClass: 'sortable-ghost',
            onEnd: () => {
                const ids = Array.from(sidebarList.querySelectorAll('[data-id]')).map(el => el.dataset.id);
                fetch('/cashtimeline/public/accounts?action=reorder', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(ids)
                });
            }
        });
    }

    // Set focus when offcanvas opens
    const txnOffcanvas = document.getElementById('txnOffcanvas');
    if (txnOffcanvas) {
        txnOffcanvas.addEventListener('shown.bs.offcanvas', () => {
            const amountInput = document.getElementById('txnAmount');
            if (amountInput) {
                amountInput.focus();
                amountInput.select();
            }
        });
    }

    const accountOffcanvas = document.getElementById('accountOffcanvas');
    if (accountOffcanvas) {
        accountOffcanvas.addEventListener('shown.bs.offcanvas', () => {
            const balanceInput = document.getElementById('accountBalance');
            if (balanceInput) {
                balanceInput.focus();
                balanceInput.select();
            }
        });
    }
});
</script>
</body>
</html>
