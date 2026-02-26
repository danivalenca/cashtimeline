<!-- Account Offcanvas — Add / Edit -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="accountOffcanvas">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="accountOffcanvasTitle">Add Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <form method="POST" action="/cashtimeline/public/accounts" id="accountForm">
            <input type="hidden" name="action" id="accountAction" value="create">
            <input type="hidden" name="id"     id="accountId"     value="">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/cashtimeline/public/accounts') ?>">

            <div class="mb-3">
                <label class="form-label-sm">Account name</label>
                <input type="text" name="name" id="accountName" class="form-control-ct"
                       placeholder="e.g. Scotiabank Joint" required>
            </div>

            <div class="mb-3">
                <label class="form-label-sm">Type</label>
                <select name="type" id="accountType" class="form-select-ct">
                    <option value="checking">Checking</option>
                    <option value="savings">Savings</option>
                    <option value="credit">Credit</option>
                    <option value="cash">Cash</option>
                    <option value="investment">Investment</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label-sm">Currency</label>
                <select name="currency" id="accountCurrency" class="form-select-ct">
                    <option value="CAD">CAD — Canadian Dollar</option>
                    <option value="USD">USD — US Dollar</option>
                    <option value="EUR">EUR — Euro</option>
                    <option value="GBP">GBP — British Pound</option>
                    <option value="BRL">BRL — Brazilian Real</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label-sm">Opening balance</label>
                <input type="number" name="initial_balance" id="accountBalance" class="form-control-ct"
                       placeholder="0.00" step="0.01" value="0" autofocus>
            </div>

            <div class="mb-4">
                <label class="form-label-sm">Colour</label>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <input type="color" name="color" id="accountColor"
                           value="#6366f1"
                           style="width:40px;height:40px;border:1px solid var(--border);border-radius:8px;padding:2px;cursor:pointer;background:none;">
                    <span style="font-size:12px;color:var(--text-muted);">Used as the account indicator colour</span>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label-sm">Sort order <span style="color:var(--text-muted);font-weight:400;">(lower = first)</span></label>
                <input type="number" name="sort_order" id="accountSortOrder" class="form-control-ct"
                       placeholder="0" value="0">
            </div>

            <div class="mb-4" id="exchangeRateSection">
                <label class="form-label-sm">Exchange rate to CAD</label>
                <div class="d-flex gap-2 align-items-center">
                    <input type="number" name="exchange_rate" id="accountExchangeRate" class="form-control-ct"
                           placeholder="1.000000" step="0.000001" min="0.000001" value="1">
                    <button type="button" id="fetchRateBtn" class="btn-ghost" title="Fetch live rate"
                            style="padding:6px 12px;white-space:nowrap;display:none;" onclick="fetchExchangeRate()">
                        <i class="fa-solid fa-rotate" id="fetchRateIcon"></i>
                    </button>
                </div>
                <div id="exchangeRateStatus" style="font-size:11px;color:var(--text-muted);margin-top:4px;">
                    CAD accounts use a fixed rate of 1.
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn-accent flex-grow-1 justify-content-center">
                    <i class="fa-solid fa-check me-1"></i> Save Account
                </button>
                <button type="button" class="btn-ghost" data-bs-dismiss="offcanvas">Cancel</button>
            </div>
        </form>

        <!-- Exchange-rate helpers (shared between accounts.php and dashboard.php) -->
        <script>
        /* Update section visibility without fetching */
        function updateRateSection() {
            const currency = document.getElementById('accountCurrency').value;
            const rateInput = document.getElementById('accountExchangeRate');
            const statusEl  = document.getElementById('exchangeRateStatus');
            const btn       = document.getElementById('fetchRateBtn');
            const section   = document.getElementById('exchangeRateSection');
            if (currency === 'CAD') {
                rateInput.value = 1;
                section.style.opacity = '0.55';
                btn.style.display = 'none';
                statusEl.textContent  = 'CAD accounts use a fixed rate of 1.';
                statusEl.style.color  = 'var(--text-muted)';
            } else {
                section.style.opacity = '1';
                btn.style.display = '';
                statusEl.textContent  = 'Click \u21BB to fetch the live rate, or enter manually.';
                statusEl.style.color  = 'var(--text-muted)';
            }
        }

        /* Fetch live rate from open.exchangerate-api.com (free, no key needed) */
        function fetchExchangeRate() {
            const currency = document.getElementById('accountCurrency').value;
            if (currency === 'CAD') { updateRateSection(); return; }
            const rateInput = document.getElementById('accountExchangeRate');
            const statusEl  = document.getElementById('exchangeRateStatus');
            const icon      = document.getElementById('fetchRateIcon');
            icon.classList.add('fa-spin');
            statusEl.textContent = 'Fetching live rate\u2026';
            statusEl.style.color = 'var(--text-muted)';
            fetch(`https://open.exchangerate-api.com/v6/latest/${currency}`)
                .then(r => r.json())
                .then(data => {
                    if (data.result === 'success' && data.rates && data.rates.CAD) {
                        rateInput.value = data.rates.CAD.toFixed(6);
                        statusEl.textContent = `1 ${currency} = ${data.rates.CAD.toFixed(4)} CAD \u00b7 fetched ${new Date().toLocaleTimeString()}`;
                        statusEl.style.color = 'var(--income-color)';
                    } else {
                        statusEl.textContent = 'Could not fetch rate \u2014 enter manually.';
                        statusEl.style.color = 'var(--expense-color)';
                    }
                })
                .catch(() => {
                    statusEl.textContent = 'Network error \u2014 enter rate manually.';
                    statusEl.style.color = 'var(--expense-color)';
                })
                .finally(() => icon.classList.remove('fa-spin'));
        }

        /* Auto-fetch when user changes the currency dropdown */
        document.addEventListener('DOMContentLoaded', () => {
            const sel = document.getElementById('accountCurrency');
            if (sel) sel.addEventListener('change', fetchExchangeRate);
        });
        </script>

        <!-- Delete zone — only shown in edit mode -->
        <div id="accountDeleteZone" class="d-none mt-3">
            <div class="divider"></div>
                <form method="POST" action="/cashtimeline/public/accounts" id="accountDeleteForm">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="accountDeleteId" value="">
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/cashtimeline/public/accounts') ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger w-100"
                        onclick="return confirm('Delete this account and all its transactions?')">
                    <i class="fa-solid fa-trash me-1"></i> Delete Account
                </button>
            </form>
        </div>
    </div>
</div>
