/**
 * CashTimeline — app.js
 * Shared utilities for all pages.
 */

/* ──────────────────────────────────────────────
   Offcanvas autofocus
   ────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    const autoFocusMap = {
        'txnOffcanvas':       'txnDescription',
        'accountOffcanvas':   'accountName',
        'recurringOffcanvas': 'recurringDescription',
    };
    Object.entries(autoFocusMap).forEach(([canvasId, inputId]) => {
        const el = document.getElementById(canvasId);
        if (!el) return;
        el.addEventListener('shown.bs.offcanvas', () => {
            const input = document.getElementById(inputId);
            if (input) { input.focus(); input.select(); }
        });
    });
});

/* ──────────────────────────────────────────────
   Mobile sidebar toggle
   ────────────────────────────────────────────── */

function openSidebar() {
    document.getElementById('mainSidebar')?.classList.add('open');
    document.getElementById('sidebarBackdrop')?.classList.add('show');
    document.body.classList.add('sidebar-open');
}
function closeSidebar() {
    document.getElementById('mainSidebar')?.classList.remove('open');
    document.getElementById('sidebarBackdrop')?.classList.remove('show');
    document.body.classList.remove('sidebar-open');
}
// Close on Escape
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSidebar(); });

/* ──────────────────────────────────────────────
   Transaction offcanvas helpers
   ────────────────────────────────────────────── */

/**
 * Reset the transaction offcanvas form to blank state.
 */
function resetTxnOffcanvas() {
    const form = document.getElementById('txnForm');
    if (form) form.reset();

    // Reset type toggle to expense
    const expenseRadio = document.getElementById('typeExpense');
    if (expenseRadio) expenseRadio.checked = true;

    // Hide context zones
    document.getElementById('txnDeleteZone')?.classList.add('d-none');
    document.getElementById('txnRecurringZone')?.classList.add('d-none');

    // Show all categories (filter will narrow them)
    filterTxnCategories('expense', null);
}

/**
 * Open the transaction offcanvas in "create" mode pre-filled with the
 * currently-displayed transaction values, so the user can edit before saving.
 */
function duplicateTxn() {
    // Capture current field values before resetting
    const account = document.getElementById('txnAccount')?.value || '';
    const desc    = document.getElementById('txnDescription')?.value || '';
    const amount  = document.getElementById('txnAmount')?.value || '';
    const date    = document.getElementById('txnDate')?.value || '';
    const type    = document.querySelector('input[name=type]:checked')?.value || 'expense';

    // Hide the current offcanvas instance
    const oc = bootstrap.Offcanvas.getInstance(document.getElementById('txnOffcanvas'));
    if (oc) oc.hide();

    // Re-open with pre-filled create state after the close animation
    setTimeout(() => {
        resetTxnOffcanvas();
        document.getElementById('txnOffcanvasTitle').textContent = 'Duplicate Transaction';
        document.getElementById('txnAction').value       = 'create';
        document.getElementById('txnId').value           = '';
        document.getElementById('txnDate').value         = date;
        document.getElementById('txnAccount').value      = account;
        document.getElementById('txnDescription').value  = desc;
        document.getElementById('txnAmount').value       = amount;
        document.querySelectorAll('input[name=type]').forEach(r => r.checked = r.value === type);
        document.getElementById('txnDeleteZone').classList.add('d-none');
        new bootstrap.Offcanvas(document.getElementById('txnOffcanvas')).show();
    }, 320);
}

/**
 * Filter category <select> in the transaction offcanvas by type.
 * @param {string} type         'income' or 'expense'
 * @param {string|null} selectedId  Pre-select this category ID
 */
function filterTxnCategories(type, selectedId) {
    const select = document.getElementById('txnCategory');
    if (!select) return;

    Array.from(select.options).forEach(opt => {
        if (!opt.value) { opt.style.display = ''; return; } // blank option always visible
        const optType = opt.getAttribute('data-type');
        opt.style.display = (optType === type) ? '' : 'none';
    });

    // Set selected value
    if (selectedId) {
        select.value = selectedId;
    } else {
        select.value = '';
    }
}

// Wire up type toggle → category filter on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    const typeRadios = document.querySelectorAll('input[name=type]');
    typeRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            filterTxnCategories(radio.value, null);
        });
    });
});

/* ──────────────────────────────────────────────
   Category colour swatch picker
   ────────────────────────────────────────────── */

/**
 * Called when a swatch div is clicked.
 * @param {HTMLElement} el           The swatch element clicked
 * @param {string} hiddenInputId     ID of the hidden color input
 * @param {string} swatchGridId      ID of the swatch container
 */
function selectSwatch(el, hiddenInputId, swatchGridId) {
    const color = el.getAttribute('data-color');
    document.getElementById(hiddenInputId).value = color;

    // Update selected state
    document.querySelectorAll(`#${swatchGridId} .color-swatch`).forEach(s => s.classList.remove('selected'));
    el.classList.add('selected');
}

/**
 * Programmatically select a swatch by colour value (used when editing).
 * @param {string} hiddenInputId
 * @param {string} swatchGridId
 * @param {string} color
 */
function selectSwatchByColor(hiddenInputId, swatchGridId, color) {
    document.getElementById(hiddenInputId).value = color;
    document.querySelectorAll(`#${swatchGridId} .color-swatch`).forEach(s => {
        s.classList.toggle('selected', s.getAttribute('data-color') === color);
    });
}

/* ──────────────────────────────────────────────
   Flash message auto-dismiss
   ────────────────────────────────────────────── */

document.addEventListener('DOMContentLoaded', () => {
    const flash = document.querySelector('.flash-bar');
    if (flash) {
        setTimeout(() => {
            flash.style.transition = 'opacity .4s';
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 450);
        }, 4000);
    }
});

/* ──────────────────────────────────────────────
   Timeline: scroll to current month on load
   ────────────────────────────────────────────── */

document.addEventListener('DOMContentLoaded', () => {
    // Always start at the leftmost column (current/start month is always first)
    const wrapper = document.querySelector('.timeline-wrapper');
    if (wrapper) wrapper.scrollLeft = 0;
});

/* ──────────────────────────────────────────────
   Recurring offcanvas: show/hide day-of-month
   Already defined inline in _recurring.php,
   kept here as fallback.
   ────────────────────────────────────────────── */
if (typeof toggleDayOfMonth === 'undefined') {
    function toggleDayOfMonth(freq) {
        const group = document.getElementById('dayOfMonthGroup');
        if (group) group.style.display = freq === 'monthly' ? '' : 'none';
    }
}
