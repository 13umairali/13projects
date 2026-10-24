export const statusColor = s => ({ open: 'error', pending: 'warning', resolved: 'success', closed: 'default' }[s] || 'default');
export const priorityColor = p => ({ urgent: 'error', high: 'warning', medium: 'processing', low: 'success' }[p] || 'processing');
export function fmtDateTime(value) {
    if (!value) return '';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return value;
    const p = n => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())} ${p(d.getHours())}:${p(d.getMinutes())}`;
}
