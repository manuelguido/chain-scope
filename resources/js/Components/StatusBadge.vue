<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: { type: String, required: true }, // success | pending | failed
});

const meta = computed(() => {
    switch (props.status) {
        case 'success':
            return { cls: 'status-badge--success', label: 'Success' };
        case 'pending':
            return { cls: 'status-badge--pending', label: 'Pending' };
        case 'failed':
            return { cls: 'status-badge--failed', label: 'Failed' };
        default:
            return { cls: '', label: props.status };
    }
});
</script>

<template>
    <span class="status-badge" :class="meta.cls">
        <span class="status-badge__dot" aria-hidden="true" />
        {{ meta.label }}
    </span>
</template>
