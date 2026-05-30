/**
 * Formatting helpers for blockchain data.
 */

const NUM = new Intl.NumberFormat('en-US');

export function fmtInt(n) {
    if (n === null || n === undefined) return '—';
    return NUM.format(n);
}

export function fmtEth(value, digits = 4) {
    if (value === null || value === undefined) return '—';
    const v = Number(value);
    if (!Number.isFinite(v)) return '—';
    if (v === 0) return '0 ETH';
    if (v < 0.0001) return v.toExponential(2) + ' ETH';
    return (
        v.toLocaleString('en-US', { maximumFractionDigits: digits }) + ' ETH'
    );
}

export function fmtUsd(value) {
    if (value === null || value === undefined) return '—';
    const v = Number(value);
    if (!Number.isFinite(v)) return '—';
    if (Math.abs(v) >= 1_000_000) return '$' + (v / 1_000_000).toFixed(2) + 'M';
    if (Math.abs(v) >= 1_000) return '$' + (v / 1_000).toFixed(2) + 'K';
    return '$' + v.toLocaleString('en-US', { maximumFractionDigits: 2 });
}

export function fmtGwei(value) {
    if (value === null || value === undefined) return '—';
    return Number(value).toFixed(2) + ' gwei';
}

export function fmtBytes(n) {
    if (!n) return '—';
    if (n < 1024) return n + ' B';
    if (n < 1_048_576) return (n / 1024).toFixed(2) + ' KB';
    return (n / 1_048_576).toFixed(2) + ' MB';
}

export function ago(unix) {
    if (!unix) return '';
    const d = Math.max(0, Math.floor(Date.now() / 1000) - unix);
    if (d < 60) return d + 's ago';
    if (d < 3600) return Math.floor(d / 60) + 'm ago';
    if (d < 86400) return Math.floor(d / 3600) + 'h ago';
    return Math.floor(d / 86400) + 'd ago';
}

export function isoTime(unix) {
    if (!unix) return '';
    return (
        new Date(unix * 1000).toISOString().replace('T', ' ').slice(0, 19) +
        ' UTC'
    );
}
