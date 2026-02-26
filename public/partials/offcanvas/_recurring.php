<?php
// _recurring.php — Recurring Rule Offcanvas Add / Edit
$accounts   = $accounts   ?? [];
$categories = $categories ?? [];
?>
<!-- Recurring Rules Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="recurringOffcanvas">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="recurringOffcanvasTitle">Add Recurring Rule</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <form method="POST" action="/cashtimeline/public/recurring" id="recurringForm">
            <input type="hidden" name="action"  id="recurringAction" value="create">
            <input type="hidden" name="id"       id="recurringId"     value="">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/cashtimeline/public/recurring') ?>">

            <!-- Type toggle -->
            <div class="mb-3">
                <label class="form-label-sm">Type</label>
                <div class="type-toggle">
                    <input type="radio" name="type" id="recTypeExpense" value="expense" checked>
                    <label for="recTypeExpense"><i class="fa-solid fa-arrow-up me-1"></i>Expense</label>
                    <input type="radio" name="type" id="recTypeIncome" value="income">
                    <label for="recTypeIncome"><i class="fa-solid fa-arrow-down me-1"></i>Income</label>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label-sm">Description</label>
                <input type="text" name="description" id="recurringDescription" class="form-control-ct"
                       placeholder="e.g. Monthly Rent" required>
            </div>

            <div class="mb-3">
                <label class="form-label-sm">Amount</label>
                <input type="number" name="amount" id="recurringAmount" class="form-control-ct"
                       placeholder="0.00" step="0.01" min="0.01" required>
            </div>

            <div class="mb-3">
                <label class="form-label-sm">Account</label>
                <select name="account_id" id="recurringAccount" class="form-select-ct" required>
                    <option value="">— Select account —</option>
                    <?php foreach ($accounts as $acct): ?>
                        <option value="<?= $acct['id'] ?>"><?= htmlspecialchars($acct['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label-sm">Frequency</label>
                <select name="frequency" id="recurringFrequency" class="form-select-ct"
                        onchange="toggleDayOfMonth(this.value)">
                    <option value="monthly" selected>Monthly</option>
                    <option value="biweekly">Biweekly (every 2 weeks)</option>
                    <option value="weekly">Weekly</option>
                    <option value="yearly">Yearly</option>
                    <option value="daily">Daily</option>
                </select>
            </div>

            <div class="mb-3" id="dayOfMonthGroup">
                <label class="form-label-sm">Day of month</label>
                <input type="number" name="day_of_month" id="recurringDayOfMonth" class="form-control-ct"
                       placeholder="1–31" min="1" max="31">
                <small style="color:var(--text-muted);font-size:11px;">Leave blank to use the start date's day</small>
            </div>

            <div class="mb-3">
                <label class="form-label-sm">Start date</label>
                <input type="date" name="start_date" id="recurringStartDate" class="form-control-ct"
                       value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="mb-4">
                <label class="form-label-sm">End date <span style="color:var(--text-muted);font-weight:400;">(optional)</span></label>
                <input type="date" name="end_date" id="recurringEndDate" class="form-control-ct">
                <small style="color:var(--text-muted);font-size:11px;">Leave blank for indefinite recurrence</small>
            </div>
            <!-- Notification Settings -->
            <div class="mb-4 pb-3" style="border-bottom:1px solid var(--border);">
                <label class="form-label-sm">Notification Reminder</label>
                <select name="notify_before_days" id="recurringNotifyBefore" class="form-select-ct">
                    <option value="0">On the day</option>
                    <option value="1" selected>1 day before</option>
                    <option value="2">2 days before</option>
                    <option value="3">3 days before</option>
                    <option value="7">1 week before</option>
                </select>
                <small style="color:var(--text-muted);font-size:11px;display:block;margin-top:4px;">
                    <i class="fa-solid fa-bell" style="color:var(--accent);font-size:10px;"></i>
                    You'll be notified based on your settings preferences
                </small>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn-accent flex-grow-1 justify-content-center">
                    <i class="fa-solid fa-check me-1"></i> Save Rule
                </button>
                <button type="button" class="btn-ghost" data-bs-dismiss="offcanvas">Cancel</button>
            </div>
        </form>

        <div id="recurringDeleteZone" class="d-none mt-3">
            <div class="divider"></div>
                <form method="POST" action="/cashtimeline/public/recurring" id="recurringDeleteForm">
                <input type="hidden" name="action"  value="delete">
                <input type="hidden" name="id"       id="recurringDeleteId" value="">
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/cashtimeline/public/recurring') ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger w-100"
                        onclick="return confirm('Delete this recurring rule?')">
                    <i class="fa-solid fa-trash me-1"></i> Delete Rule
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function toggleDayOfMonth(freq) {
    document.getElementById('dayOfMonthGroup').style.display = freq === 'monthly' ? '' : 'none';
}
</script>
