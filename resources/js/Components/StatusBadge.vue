<script setup>
import { computed } from 'vue';
import { CheckCircle2, Clock, XCircle } from 'lucide-vue-next';

const props = defineProps({
    status: { type: String, required: true }, // success | pending | failed
});

const meta = computed(() => {
    switch (props.status) {
        case 'success':
            return { cls: 'pill--ok', label: 'Success', icon: CheckCircle2 };
        case 'pending':
            return { cls: 'pill--warn', label: 'Pending', icon: Clock };
        case 'failed':
            return { cls: 'pill--err', label: 'Failed', icon: XCircle };
        default:
            return { cls: 'pill', label: props.status, icon: Clock };
    }
});
</script>

<template>
    <span class="pill" :class="meta.cls">
        <component :is="meta.icon" :size="11" :stroke-width="2.2" />
        {{ meta.label }}
    </span>
</template>
