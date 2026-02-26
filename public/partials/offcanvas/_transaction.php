<?php
// _transaction.php — Transaction Add / Edit offcanvas
// Requires $accounts and $categories to be available in scope
$accounts   = $accounts   ?? [];
$categories = $categories ?? [];
?>
<!-- Transaction Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="txnOffcanvas">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="txnOffcanvasTitle">Add Transaction</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <form method="POST" action="/cashtimeline/public/transactions" id="txnForm">
            <input type="hidden" name="action"   id="txnAction" value="create">
            <input type="hidden" name="id"        id="txnId"     value="">
            <input type="hidden" name="redirect"  value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/cashtimeline/public/dashboard') ?>">

            <!-- Type toggle -->
            <div class="mb-3">
                <label class="form-label-sm">Type</label>
                <div class="type-toggle">
                    <input type="radio" name="type" id="typeExpense" value="expense" checked>
                    <label for="typeExpense"><i class="fa-solid fa-arrow-up me-1"></i>Expense</label>
                    <input type="radio" name="type" id="typeIncome" value="income">
                    <label for="typeIncome"><i class="fa-solid fa-arrow-down me-1"></i>Income</label>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label-sm">Date</label>
                <input type="date" name="transaction_date" id="txnDate" class="form-control-ct"
                       value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label-sm">Account</label>
                <select name="account_id" id="txnAccount" class="form-select-ct" required>
                    <option value="">— Select account —</option>
                    <?php foreach ($accounts as $acct): ?>
                        <option value="<?= $acct['id'] ?>"><?= htmlspecialchars($acct['name']) ?> (<?= $acct['currency'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label-sm">Description</label>
                <input type="text" name="description" id="txnDescription" class="form-control-ct"
                       placeholder="e.g. Monthly rent" required>
            </div>

            <div class="mb-4">
                <label class="form-label-sm">Amount</label>
                <div class="d-flex align-items-center gap-2">
                    <span style="font-size:13px;color:var(--text-muted);flex-shrink:0;">CA$</span>
                    <input type="number" name="amount" id="txnAmount" class="form-control-ct"
                           placeholder="0.00" step="0.01" min="0.01" required autofocus>
                </div>
            </div>

            <!-- Notification Toggle -->
            <div class="mb-4">
                <div class="form-check form-switch" style="padding-left:0;">
                    <label class="form-check-label d-flex align-items-center gap-2" style="cursor:pointer;">
                        <input type="checkbox" name="notify_on_date" id="txnNotify" 
                               class="form-check-input" value="1" style="cursor:pointer;margin:0;">
                        <div>
                            <i class="fa-solid fa-bell" style="color:var(--accent);font-size:13px;margin-right:4px;"></i>
                            <span style="font-size:13px;font-weight:500;">Notify me about this transaction</span>
                            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                                You'll be notified based on your settings preferences
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn-accent flex-grow-1 justify-content-center">
                    <i class="fa-solid fa-check me-1"></i> Save Transaction
                </button>
                <button type="button" class="btn-ghost" data-bs-dismiss="offcanvas">Cancel</button>
            </div>
        </form>

        <!-- Recurring occurrence zone (shown when recording a recurring occurrence) -->
        <div id="txnRecurringZone" class="d-none mt-3">
            <div class="divider"></div>
            
            <!-- Process Recurring Button -->
            <div class="process-info" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px;margin-bottom:12px;">
                <div style="font-size:12px;color:#166534;margin-bottom:8px;">
                    <i class="fa-solid fa-circle-check" style="color:#22c55e;"></i>
                    <strong>Process & update balance?</strong>
                </div>
                <div style="font-size:11px;color:#166534;margin-bottom:10px;line-height:1.4;">
                    This will record this occurrence, update your account balance, and mark it as processed (it will stay visible but won't affect future calculations).
                </div>
                <button type="button" class="btn btn-sm w-100" 
                        style="background:#22c55e;color:white;border:none;margin-bottom:8px;"
                        onclick="processRecurringOccurrence()">
                    <i class="fa-solid fa-check-double me-1"></i> Process & Update Balance
                </button>
            </div>
            
            <p style="font-size:12px;color:var(--text-muted);margin-bottom:8px;">
                <i class="fa-solid fa-rotate me-1" style="color:var(--accent);"></i>
                Or just save to record <strong>this occurrence only</strong> without processing.
            </p>
            <a id="txnEditRuleLink" href="/cashtimeline/public/recurring"
               class="btn btn-sm btn-outline-secondary w-100">
                <i class="fa-solid fa-pen-to-square me-1"></i> Edit recurring rule (all upcoming)
            </a>
        </div>

        <!-- Duplicate + Delete zone (edit mode only) -->
        <div id="txnDeleteZone" class="d-none mt-3">
            <div class="divider"></div>
            
            <!-- Process Transaction -->
            <div class="process-info" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px;margin-bottom:12px;">
                <div style="font-size:12px;color:#166534;margin-bottom:8px;">
                    <i class="fa-solid fa-circle-check" style="color:#22c55e;"></i>
                    <strong>Process this transaction?</strong>
                </div>
                <div style="font-size:11px;color:#166534;margin-bottom:10px;line-height:1.4;">
                    This will update the account's opening balance to reflect this transaction and mark it as processed (it will stay visible but won't affect future balance calculations).
                </div>
                <form method="POST" action="/cashtimeline/public/transactions" id="txnProcessForm">
                    <input type="hidden" name="action" value="process">
                    <input type="hidden" name="id" id="txnProcessId" value="">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/cashtimeline/public/dashboard') ?>">
                    <button type="submit" class="btn btn-sm w-100" 
                            style="background:#22c55e;color:white;border:none;"
                            onclick="return confirm('Process this transaction and update account balance?')">
                        <i class="fa-solid fa-check-double me-1"></i> Process & Update Balance
                    </button>
                </form>
            </div>
            
            <!-- Duplicate -->
            <button type="button" class="btn btn-sm btn-outline-secondary w-100 mb-2" onclick="duplicateTxn()">
                <i class="fa-solid fa-copy me-1"></i> Duplicate Transaction
            </button>
            
            <!-- Delete -->
            <form method="POST" action="/cashtimeline/public/transactions" id="txnDeleteForm">
                <input type="hidden" name="action"   value="delete">
                <input type="hidden" name="id"        id="txnDeleteId" value="">
                <input type="hidden" name="redirect"  value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/cashtimeline/public/dashboard') ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger w-100"
                        onclick="return confirm('Delete this transaction?')">
                    <i class="fa-solid fa-trash me-1"></i> Delete Transaction
                </button>
            </form>
        </div>
    </div>
</div>
