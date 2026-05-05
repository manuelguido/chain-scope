<script setup>
import { Head } from '@inertiajs/vue3';
import { SearchX } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchBar from '@/Components/SearchBar.vue';

defineProps({ query: String });
</script>

<template>
    <Head title="No results" />
    <AppLayout :show-search="false">
        <div class="py-16 max-w-xl mx-auto text-center">
            <div class="w-12 h-12 mx-auto mb-4 grid place-items-center rounded-lg border border-[color:var(--color-line-strong)] bg-[color:var(--color-surface-2)] text-[color:var(--color-fg-muted)]">
                <SearchX :size="20" :stroke-width="2" />
            </div>
            <h1 class="text-xl font-semibold mb-2">No results</h1>
            <p class="text-sm text-[color:var(--color-fg-muted)] mb-6">
                We couldn't recognize <code class="mono text-[color:var(--color-fg)]">{{ query }}</code> as a transaction hash, block number, address, or ENS name.
            </p>
            <SearchBar hero />
            <div class="mt-6 text-xs text-[color:var(--color-fg-dim)] mono">
                Examples: <code>0xd8da6bf2…</code> · <code>22104150</code> · <code>vitalik.eth</code>
            </div>
        </div>
    </AppLayout>
</template>
