import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// ── Money formatter helpers (IDR thousand separators) ──────────────
// Used by:
//   1. Plain inputs marked with `data-money` (auto-bound, see below)
//   2. Alpine-driven inputs via :value="formatRupiah(state)" @input handlers
//
// formatRupiah("1500000")   → "1.500.000"
// parseRupiah("1.500.000")  → "1500000"
window.formatRupiah = function (value) {
    if (value === null || value === undefined || value === '') return '';
    const digits = String(value).replace(/[^\d]/g, '');
    if (!digits) return '';
    return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

window.parseRupiah = function (value) {
    return String(value ?? '').replace(/[^\d]/g, '');
};

// Bind a form so any [data-money] / [data-money-alpine] input gets its
// value stripped to a raw integer right before the form is submitted.
function bindMoneyForm(form) {
    if (!form || form.dataset.moneyFormBound === '1') return;
    form.dataset.moneyFormBound = '1';
    form.addEventListener('submit', () => {
        form.querySelectorAll('input[data-money], input[data-money-alpine]').forEach((i) => {
            i.value = window.parseRupiah(i.value);
        });
    });
}

// Auto-format any <input data-money>. Switches the input to type="text"
// with inputmode="numeric", reformats while typing, and strips dots before
// the form is submitted so the backend still receives a raw integer.
function bindMoneyInput(input) {
    if (input.dataset.moneyBound === '1') return;
    input.dataset.moneyBound = '1';

    if (input.type !== 'text') input.type = 'text';
    input.setAttribute('inputmode', 'numeric');
    input.setAttribute('autocomplete', 'off');

    if (input.value) input.value = window.formatRupiah(input.value);

    input.addEventListener('input', (e) => {
        const el = e.target;
        const cursor = el.selectionStart ?? el.value.length;
        const before = el.value;
        const dotsBefore = (before.slice(0, cursor).match(/\./g) || []).length;
        const formatted = window.formatRupiah(before);
        el.value = formatted;
        const dotsAfter = (formatted.slice(0, cursor).match(/\./g) || []).length;
        try {
            const newPos = Math.max(0, cursor + (dotsAfter - dotsBefore));
            el.setSelectionRange(newPos, newPos);
        } catch (_) {}
    });

    bindMoneyForm(input.closest('form'));
}

function initMoneyInputs(root) {
    const scope = root || document;
    scope.querySelectorAll('input[data-money]').forEach(bindMoneyInput);
    // For Alpine-driven money inputs (use :value="formatRupiah(...)" pattern):
    // we don't touch the input itself — Alpine owns rendering — but we still
    // need to strip the dots before the form submits.
    scope.querySelectorAll('input[data-money-alpine]').forEach((input) => {
        bindMoneyForm(input.closest('form'));
    });
}

window.initMoneyInputs = initMoneyInputs;
window.bindMoneyInput = bindMoneyInput;

document.addEventListener('DOMContentLoaded', () => initMoneyInputs());
document.addEventListener('alpine:initialized', () => initMoneyInputs());

Alpine.start();
