<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';

const props = defineProps({
    hero: { type: Boolean, default: false },
    placeholder: {
        type: String,
        default: 'Search by address, transaction hash, block number or ENS',
    },
});

const q = ref('');
const inputEl = ref(null);
const isSubmitting = ref(false);
const inputId = `chain-search-${Math.random().toString(36).slice(2)}`;

function submit() {
    const value = q.value.trim();
    if (!value) return;

    router.visit(`/search?q=${encodeURIComponent(value)}`, {
        onStart: () => {
            isSubmitting.value = true;
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
}

function onKey(e) {
    if (
        e.key === '/' &&
        !['INPUT', 'TEXTAREA'].includes(document.activeElement?.tagName)
    ) {
        e.preventDefault();
        inputEl.value?.focus();
    }
}

onMounted(() => window.addEventListener('keydown', onKey));
onBeforeUnmount(() => window.removeEventListener('keydown', onKey));
</script>

<template>
    <form
        class="search"
        :class="{ 'search--hero': hero }"
        role="search"
        :aria-label="hero ? 'Explore Ethereum search' : 'Header search'"
        @submit.prevent="submit"
    >
        <label class="sr-only" :for="inputId">Search Ethereum</label>
        <Search class="search__icon" :size="hero ? 18 : 14" :stroke-width="2" />
        <input
            :id="inputId"
            ref="inputEl"
            v-model="q"
            type="text"
            class="search__input"
            :placeholder="placeholder"
            spellcheck="false"
            autocomplete="off"
            enterkeyhint="search"
        />
        <!-- <span class="search__hint" aria-hidden="true">/</span> -->
        <button
            v-if="hero"
            class="search__submit"
            type="submit"
            :disabled="!q.trim() || isSubmitting"
        >
            <Search :size="14" :stroke-width="2.2" aria-hidden="true" />
            <span>{{ isSubmitting ? 'Searching' : 'Search' }}</span>
        </button>
    </form>
</template>
