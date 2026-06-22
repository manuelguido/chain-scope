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
        <section class="section mx-auto max-w-xl">
            <div class="section__body section__body--padded py-10 text-center">
                <div
                    class="mx-auto mb-4 grid h-12 w-12 place-items-center rounded-[var(--radius-md)] border border-[color:var(--color-line)] bg-[color:var(--color-surface-2)] text-[color:var(--color-fg-muted)]"
                >
                    <SearchX :size="20" :stroke-width="2" />
                </div>
                <h1 class="mb-2 text-xl font-semibold">No results</h1>
                <p class="mb-6 text-sm text-[color:var(--color-fg-muted)]">
                    We couldn't recognize
                    <code class="mono text-[color:var(--color-fg)]">{{
                        query
                    }}</code>
                    as a transaction hash, block number, address, or ENS name.
                </p>
                <SearchBar hero />
                <div
                    class="technical-value mt-6 text-xs text-[color:var(--color-fg-dim)]"
                >
                    Examples: <code>0xd8da6bf2...</code> ·
                    <code>22104150</code> · <code>vitalik.eth</code>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
