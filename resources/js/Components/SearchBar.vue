<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';

const props = defineProps({
    hero: { type: Boolean, default: false },
    placeholder: {
        type: String,
        default: 'Search by address, transaction hash, block, or ENS',
    },
});

const q = ref('');
const inputEl = ref(null);

function submit() {
    const value = q.value.trim();
    if (!value) return;
    router.visit(`/search?q=${encodeURIComponent(value)}`);
}

function onKey(e) {
    if (e.key === '/' && !['INPUT', 'TEXTAREA'].includes(document.activeElement?.tagName)) {
        e.preventDefault();
        inputEl.value?.focus();
    }
}

onMounted(() => window.addEventListener('keydown', onKey));
onBeforeUnmount(() => window.removeEventListener('keydown', onKey));
</script>

<template>
    <form class="search" :class="{ 'search--hero': hero }" @submit.prevent="submit">
        <Search class="search__icon" :size="hero ? 18 : 14" :stroke-width="2" />
        <input
            ref="inputEl"
            v-model="q"
            type="text"
            class="search__input"
            :placeholder="placeholder"
            spellcheck="false"
            autocomplete="off"
        />
        <span v-if="!hero" class="search__hint">/</span>
    </form>
</template>
