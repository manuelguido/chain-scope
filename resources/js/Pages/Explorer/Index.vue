<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import {
    Activity,
    ArrowUpRight,
    Blocks,
    Database,
    Fuel,
    Gauge,
    RadioTower,
    Search,
    ShieldCheck,
    Wallet,
} from 'lucide-vue-next';

const props = defineProps({
    stats: Array,
    networks: Array,
    blocks: Array,
    transactions: Array,
    addresses: Array,
});

const query = ref('0xb9a4...7e12');

const lookupItems = computed(() => [
    ...props.transactions.map((transaction) => ({
        type: 'Transaction',
        title: transaction.hash,
        subtitle: `${transaction.method} · ${transaction.value}`,
        meta: transaction.status,
        payload: transaction,
    })),
    ...props.blocks.map((block) => ({
        type: 'Block',
        title: block.height,
        subtitle: `${block.txs} txs · ${block.gasUsed} gas`,
        meta: block.age,
        payload: block,
    })),
    ...props.addresses.map((address) => ({
        type: 'Address',
        title: address.address,
        subtitle: `${address.label} · ${address.balance}`,
        meta: address.risk,
        payload: address,
    })),
]);

const selectedResult = computed(() => {
    const normalizedQuery = query.value.trim().toLowerCase();

    if (!normalizedQuery) {
        return lookupItems.value[0];
    }

    return lookupItems.value.find((item) => {
        return `${item.title} ${item.subtitle} ${item.type}`.toLowerCase().includes(normalizedQuery);
    }) ?? lookupItems.value[0];
});

const demoLookups = computed(() => [
    lookupItems.value.find((item) => item.type === 'Transaction'),
    lookupItems.value.find((item) => item.type === 'Block'),
    lookupItems.value.find((item) => item.type === 'Address'),
].filter(Boolean));

const toneClass = (tone) => {
    return {
        cyan: 'text-cyan-300 bg-cyan-400/10 border-cyan-400/20',
        emerald: 'text-emerald-300 bg-emerald-400/10 border-emerald-400/20',
        blue: 'text-blue-300 bg-blue-400/10 border-blue-400/20',
        slate: 'text-slate-300 bg-slate-700/40 border-slate-600/40',
    }[tone] ?? 'text-slate-300 bg-slate-700/40 border-slate-600/40';
};
</script>

<template>
    <Head title="Blockchain Explorer" />

    <main class="min-h-screen overflow-hidden bg-slate-950 text-slate-100">
        <div class="pointer-events-none fixed inset-0 -z-10">
            <div class="absolute left-1/2 top-0 h-[520px] w-[760px] -translate-x-1/2 rounded-full bg-cyan-500/10 blur-3xl" />
            <div class="absolute bottom-0 right-0 h-[440px] w-[440px] rounded-full bg-blue-600/10 blur-3xl" />
            <div class="absolute inset-0 bg-[linear-gradient(rgba(148,163,184,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,0.03)_1px,transparent_1px)] bg-[size:36px_36px]" />
        </div>

        <section class="mx-auto flex min-h-screen max-w-7xl flex-col px-5 py-5 sm:px-8 lg:px-10">
            <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-800/80 pb-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg border border-cyan-400/30 bg-cyan-400/10 text-cyan-300 shadow-lg shadow-cyan-500/10">
                        <Blocks class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-cyan-300">ChainScope</p>
                        <h1 class="text-lg font-semibold tracking-tight text-white">Blockchain Explorer</h1>
                    </div>
                </div>

                <div class="flex items-center gap-2 rounded-lg border border-emerald-400/20 bg-emerald-400/10 px-3 py-2 text-xs font-medium text-emerald-300">
                    <RadioTower class="h-4 w-4" />
                    Mainnet live
                </div>
            </header>

            <div class="grid flex-1 gap-5 py-5 lg:grid-cols-[1.1fr_0.9fr]">
                <section class="space-y-5">
                    <div class="rounded-lg border border-slate-800 bg-slate-900/70 p-4 shadow-2xl shadow-black/30 backdrop-blur-xl sm:p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
                            <div class="relative flex-1">
                                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" />
                                <input
                                    v-model="query"
                                    class="h-12 w-full rounded-lg border border-slate-700 bg-slate-950/70 pl-10 pr-4 font-mono text-sm text-slate-100 outline-none transition placeholder:text-slate-600 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20"
                                    placeholder="Address, block, or transaction"
                                >
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="item in demoLookups"
                                    :key="item.title"
                                    class="rounded-md border border-slate-700 px-3 py-2 text-xs text-slate-300 transition hover:border-cyan-400/50 hover:bg-cyan-400/10 hover:text-cyan-200"
                                    @click="query = item.title"
                                >
                                    {{ item.type }}
                                </button>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            <div
                                v-for="stat in stats"
                                :key="stat.label"
                                class="rounded-lg border border-slate-800 bg-slate-950/50 p-4"
                            >
                                <p class="text-xs text-slate-500">{{ stat.label }}</p>
                                <div class="mt-3 flex items-end justify-between gap-3">
                                    <p class="text-xl font-semibold tracking-tight text-white">{{ stat.value }}</p>
                                    <span class="rounded-md border px-2 py-1 text-[11px] font-medium" :class="toneClass(stat.tone)">
                                        {{ stat.change }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-5 xl:grid-cols-[0.95fr_1.05fr]">
                        <section class="rounded-lg border border-slate-800 bg-slate-900/70 p-5 shadow-xl shadow-black/20">
                            <div class="mb-4 flex items-center justify-between">
                                <h2 class="text-sm font-semibold text-slate-100">Latest blocks</h2>
                                <Gauge class="h-4 w-4 text-cyan-300" />
                            </div>

                            <div class="space-y-3">
                                <div
                                    v-for="block in blocks"
                                    :key="block.height"
                                    class="rounded-lg border border-slate-800 bg-slate-950/50 p-4 transition hover:border-cyan-400/30"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="font-mono text-sm font-semibold text-cyan-200">#{{ block.height }}</p>
                                            <p class="mt-1 text-xs text-slate-500">{{ block.validator }} · {{ block.age }}</p>
                                        </div>
                                        <span class="rounded-md bg-slate-800 px-2 py-1 text-xs text-slate-300">{{ block.txs }} txs</span>
                                    </div>
                                    <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                                        <div>
                                            <p class="text-slate-500">Gas used</p>
                                            <p class="mt-1 font-medium text-slate-200">{{ block.gasUsed }}</p>
                                        </div>
                                        <div>
                                            <p class="text-slate-500">Reward</p>
                                            <p class="mt-1 font-medium text-slate-200">{{ block.reward }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-lg border border-slate-800 bg-slate-900/70 p-5 shadow-xl shadow-black/20">
                            <div class="mb-4 flex items-center justify-between">
                                <h2 class="text-sm font-semibold text-slate-100">Transaction stream</h2>
                                <Activity class="h-4 w-4 text-cyan-300" />
                            </div>

                            <div class="overflow-hidden rounded-lg border border-slate-800">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-slate-950/80 text-xs uppercase tracking-wide text-slate-500">
                                        <tr>
                                            <th class="px-4 py-3 font-medium">Hash</th>
                                            <th class="px-4 py-3 font-medium">Method</th>
                                            <th class="px-4 py-3 font-medium">Value</th>
                                            <th class="px-4 py-3 font-medium">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-800 bg-slate-950/30">
                                        <tr v-for="transaction in transactions" :key="transaction.hash" class="transition hover:bg-slate-800/40">
                                            <td class="px-4 py-3 font-mono text-xs text-cyan-200">{{ transaction.hash }}</td>
                                            <td class="px-4 py-3 text-slate-300">{{ transaction.method }}</td>
                                            <td class="px-4 py-3 font-medium text-slate-100">{{ transaction.value }}</td>
                                            <td class="px-4 py-3">
                                                <span class="rounded-md px-2 py-1 text-xs font-medium" :class="transaction.status === 'Success' ? 'bg-emerald-400/10 text-emerald-300' : 'bg-amber-400/10 text-amber-300'">
                                                    {{ transaction.status }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                </section>

                <aside class="space-y-5">
                    <section class="rounded-lg border border-cyan-400/20 bg-cyan-400/[0.04] p-5 shadow-2xl shadow-cyan-950/20">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-cyan-300">Lookup result</p>
                                <h2 class="mt-2 font-mono text-lg font-semibold text-white">{{ selectedResult.title }}</h2>
                            </div>
                            <span class="rounded-md border border-cyan-400/20 bg-cyan-400/10 px-2 py-1 text-xs font-medium text-cyan-200">
                                {{ selectedResult.type }}
                            </span>
                        </div>

                        <p class="mt-4 text-sm text-slate-400">{{ selectedResult.subtitle }}</p>

                        <div class="mt-5 grid gap-3 text-sm">
                            <div class="flex items-center justify-between rounded-lg border border-slate-800 bg-slate-950/50 px-4 py-3">
                                <span class="text-slate-500">Network</span>
                                <span class="font-medium text-slate-100">Ethereum</span>
                            </div>
                            <div class="flex items-center justify-between rounded-lg border border-slate-800 bg-slate-950/50 px-4 py-3">
                                <span class="text-slate-500">State</span>
                                <span class="font-medium text-emerald-300">{{ selectedResult.meta }}</span>
                            </div>
                            <div class="flex items-center justify-between rounded-lg border border-slate-800 bg-slate-950/50 px-4 py-3">
                                <span class="text-slate-500">Indexed</span>
                                <span class="font-medium text-slate-100">12 sec ago</span>
                            </div>
                        </div>

                        <button class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-cyan-400 px-4 py-3 text-sm font-semibold text-slate-950 transition hover:bg-cyan-300">
                            Inspect entity
                            <ArrowUpRight class="h-4 w-4" />
                        </button>
                    </section>

                    <section class="rounded-lg border border-slate-800 bg-slate-900/70 p-5 shadow-xl shadow-black/20">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-slate-100">Networks</h2>
                            <Database class="h-4 w-4 text-cyan-300" />
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="network in networks"
                                :key="network.name"
                                class="grid grid-cols-[1fr_auto] gap-3 rounded-lg border border-slate-800 bg-slate-950/50 p-4"
                            >
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full bg-emerald-400" />
                                        <p class="font-medium text-slate-100">{{ network.name }}</p>
                                    </div>
                                    <p class="mt-2 text-xs text-slate-500">{{ network.settlement }} daily settlement</p>
                                </div>
                                <div class="text-right text-xs">
                                    <p class="font-mono text-slate-200">{{ network.tps }} TPS</p>
                                    <p class="mt-2 text-slate-500">{{ network.gas }}</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                        <div class="rounded-lg border border-slate-800 bg-slate-900/70 p-4">
                            <Wallet class="h-4 w-4 text-cyan-300" />
                            <p class="mt-3 text-xs text-slate-500">Tracked addresses</p>
                            <p class="mt-1 text-2xl font-semibold text-white">3</p>
                        </div>
                        <div class="rounded-lg border border-slate-800 bg-slate-900/70 p-4">
                            <Fuel class="h-4 w-4 text-cyan-300" />
                            <p class="mt-3 text-xs text-slate-500">Avg fee</p>
                            <p class="mt-1 text-2xl font-semibold text-white">$3.35</p>
                        </div>
                        <div class="rounded-lg border border-slate-800 bg-slate-900/70 p-4">
                            <ShieldCheck class="h-4 w-4 text-cyan-300" />
                            <p class="mt-3 text-xs text-slate-500">Risk engine</p>
                            <p class="mt-1 text-2xl font-semibold text-white">On</p>
                        </div>
                    </section>
                </aside>
            </div>
        </section>
    </main>
</template>