<script setup>
defineProps({
    value: { type: String, required: true },
    /** "full" | "tx" (10/8) | "address" (6/4) | "block" (6/6) | "compact" (4/4) */
    truncate: { type: String, default: 'tx' },
});

function trunc(value, mode) {
    if (!value) return '';
    if (mode === 'full') return value;
    const map = {
        tx: [10, 8],
        address: [6, 4],
        block: [10, 8],
        compact: [4, 4],
    };
    const [head, tail] = map[mode] || map.tx;
    if (value.length <= head + tail + 3) return value;
    return value.slice(0, head) + '…' + value.slice(-tail);
}
</script>

<template>
    <span class="mono" :title="value">{{ trunc(value, truncate) }}</span>
</template>
