<script setup>
import { onBeforeUnmount, ref } from 'vue';
import { Check, Copy } from 'lucide-vue-next';

const props = defineProps({
    value: { type: String, required: true },
    /** "full" | "tx" (10/8) | "address" (6/4) | "block" (6/6) | "compact" (4/4) */
    truncate: { type: String, default: 'tx' },
    copy: { type: Boolean, default: false },
    label: { type: String, default: 'value' },
});

const copied = ref(false);
let resetTimer = null;

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

async function copyValue(event) {
    event.preventDefault();
    event.stopPropagation();

    try {
        if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(props.value);
        } else {
            const textarea = document.createElement('textarea');
            textarea.value = props.value;
            textarea.setAttribute('readonly', '');
            textarea.style.position = 'absolute';
            textarea.style.left = '-9999px';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
        }

        copied.value = true;
        clearTimeout(resetTimer);
        resetTimer = setTimeout(() => {
            copied.value = false;
        }, 1400);
    } catch (e) {
        copied.value = false;
    }
}

onBeforeUnmount(() => clearTimeout(resetTimer));
</script>

<template>
    <span class="hash" :class="{ 'hash--copyable': copy }">
        <span class="hash__value mono" :title="value">{{
            trunc(value, truncate)
        }}</span>
        <button
            v-if="copy"
            type="button"
            class="hash__copy"
            :aria-label="copied ? `Copied ${label}` : `Copy ${label}`"
            :title="copied ? 'Copied' : 'Copy'"
            @click="copyValue"
        >
            <component
                :is="copied ? Check : Copy"
                :size="13"
                :stroke-width="2.2"
                aria-hidden="true"
            />
        </button>
        <span v-if="copy" class="hash__feedback" aria-live="polite">
            {{ copied ? 'Copied' : '' }}
        </span>
    </span>
</template>
