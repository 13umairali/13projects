import { ref } from 'vue';
import { t as tf } from '../../i18n';

/**
 * Small shared helpers for the billing pages (legacy formatDate/capitalize +
 * the currency symbol every billing endpoint returns as `currency_symbol`).
 */
export function useBillingFormat() {
    const currencySymbol = ref('$');

    const fmtDate = d => (d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '—');
    const capitalize = s => (s ? s.charAt(0).toUpperCase() + s.slice(1) : '');
    const cycleLabel = c => (c === 'yearly' ? tf('Yearly', 'Yearly') : tf('Monthly', 'Monthly'));
    const money = (amount, currency) => `${currencySymbol.value}${(Number(amount) || 0).toFixed(2)}${currency ? ' ' + currency : ''}`;
    const statusColor = s => ({
        active: 'success', paid: 'success', trial: 'processing', pending: 'warning',
        cancelled: 'default', expired: 'error', failed: 'error', refunded: 'default',
    }[s] || 'default');

    return { currencySymbol, fmtDate, capitalize, cycleLabel, money, statusColor };
}
