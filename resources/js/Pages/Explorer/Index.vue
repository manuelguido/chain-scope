<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Box, ArrowRightLeft, Activity, Fuel, Coins, Layers } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchBar from '@/Components/SearchBar.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Hash from '@/Components/Hash.vue';
import { fmtInt, fmtEth, fmtUsd, fmtGwei } from '@/utils/format.js';

const props = defineProps({
    tip: Number,
    gas_gwei: Number,
    eth_price: Number,
    blocks: Array,
    transactions: Array,
});

const tip = ref(props.tip);
const gas = ref(props.gas_gwei);
const ethPrice = ref(props.eth_price);
const blocks = ref([...props.blocks]);
const transactions = ref([...props.transactions]);
const flashedBlocks = ref(new Set());
const flashedTxs = ref(new Set());

let timer = null;

async function poll() {
    try {
        const [tipRes, blocksRes, txsRes] = await Promise.all([
            fetch('/api/live/tip', { headers: { Accept: 'application/json' } }),
            fetch('/api/live/blocks', { headers: { Accept: 'application/json' } }),
            fetch('/api/live/transactions', { headers: { Accept: 'application/json' } }),
        ]);
        if (!tipRes.ok || !blocksRes.ok || !txsRes.ok) return;
        const tipData = await tipRes.json();
        const blocksData = await blocksRes.json();
        const txsData = await txsRes.json();

        tip.value = tipData.tip;
        gas.value = tipData.gas_gwei;
        ethPrice.value = tipData.eth_price;

        // Detect new blocks for flash highlight
        const existingBlockHeights = new Set(blocks.value.map((b) => b.height));
        blocks.value = blocksData.blocks;
        const newFlash = new Set();
        for (const b of blocks.value) {
            if (!existingBlockHeights.has(b.height)) newFlash.add(b.height);
        }
        flashedBlocks.value = newFlash;
        setTimeout(() => (flashedBlocks.value = new Set()), 1200);

        const existingTxHashes = new Set(transactions.value.map((t) => t.hash));
        transactions.value = txsData.transactions;
        const newTxFlash = new Set();
        for (const t of transactions.value) {
            if (!existingTxHashes.has(t.hash)) newTxFlash.add(t.hash);
        }
        flashedTxs.value = newTxFlash;
        setTimeout(() => (flashedTxs.value = new Set()), 1200);
    } catch (e) {
        // silent
    }
}

onMounted(() => {
    timer = setInterval(poll, 6000);
});
onBeforeUnmount(() => {
    if (timer) clearInterval(timer);
});
</script>

<template>
    <Head title="Explorer" />
    <AppLayout :show-search="false">
        <!-- Hero search -->
        <section class="py-10 md:py-14">
            <div class="text-center mb-6">
                <h1 class="text-2xl md:text-3xl font-semibold tracking-tight">Ethereum Blockchain Explorer</h1>
                <p class="text-sm text-[color:var(--color-fg-muted)] mt-2">
                    Search any address, transaction, block, or ENS name
                </p>
            </div>
            <SearchBar hero />
        </section>

        <!-- Stat strip -->
        <div class="stat-strip mb-6">
            <div class="stat-cell">
                <div class="stat-cell__label">Latest block</div>
                <div class="stat-cell__value">#{{ fmtInt(tip) }}</div>
                <div class="stat-cell__meta flex items-center gap-1.5">
                    <span class="live-dot" /> Live · 12s avg
                </div>
            </div>
            <div class="stat-cell">
                <div class="stat-cell__label">Gas (base)</div>
                <div class="stat-cell__value">{{ fmtGwei(gas) }}</div>
                <div class="stat-cell__meta">≈ {{ fmtUsd(gas * 21000 * ethPrice / 1e9) }} transfer</div>
            </div>
            <div class="stat-cell">
                <div class="stat-cell__label">ETH price</div>
                <div class="stat-cell__value">{{ fmtUsd(ethPrice) }}</div>
                <div class="stat-cell__meta">USD · spot</div>
            </div>
            <div class="stat-cell">
                <div class="stat-cell__label">Network</div>
                <div class="stat-cell__value">Mainnet</div>
                <div class="stat-cell__meta">Chain ID 1 · PoS</div>
            </div>
        </div>

        <!-- Latest activity split -->
        <div class="split-2">
            <!-- Latest blocks -->
            <div class="section">
                <div class="section__head">
                    <div class="flex items-center gap-2">
                        <Box :size="14" :stroke-width="2" class="text-[color:var(--color-fg-muted)]" />
                        <span class="section__title">Latest blocks</span>
                    </div>
                    <span class="section__sub">Live</span>
                </div>
                <div class="data-list">
                    <Link
                        v-for="b in blocks"
                        :key="b.height"
                        :href="`/block/${b.height}`"
                        class="data-row row-block"
                        :class="{ 'row-flash': flashedBlocks.has(b.height) }"
                    >
                        <div class="row-icon"><Layers :size="14" :stroke-width="2" /></div>
                        <div class="field">
                            <div class="field__value field__value--mono">#{{ fmtInt(b.height) }}</div>
                            <div class="field__sub">{{ b.age }}</div>
                        </div>
                        <div class="field">
                            <div class="field__label">Validator</div>
                            <div class="field__value">{{ b.validator }}</div>
                        </div>
                        <div class="field text-right">
                            <div class="field__label">Txs</div>
                            <div class="field__value field__value--mono">{{ fmtInt(b.tx_count) }}</div>
                        </div>
                        <div class="field text-right">
                            <div class="field__label">Reward</div>
                            <div class="field__value field__value--mono">{{ b.reward_eth }} ETH</div>
                        </div>
                    </Link>
                </div>
                <div class="section__foot text-[color:var(--color-fg-muted)]">Updated every 6s</div>
            </div>

            <!-- Latest transactions -->
            <div class="section">
                <div class="section__head">
                    <div class="flex items-center gap-2">
                        <ArrowRightLeft :size="14" :stroke-width="2" class="text-[color:var(--color-fg-muted)]" />
                        <span class="section__title">Latest transactions</span>
                    </div>
                    <span class="section__sub">Live</span>
                </div>
                <div class="data-list">
                    <Link
                        v-for="t in transactions"
                        :key="t.hash"
                        :href="`/tx/${t.hash}`"
                        class="data-row row-tx"
                        :class="{ 'row-flash': flashedTxs.has(t.hash) }"
                    >
                        <div class="row-icon"><Activity :size="14" :stroke-width="2" /></div>
                        <div class="field">
                            <div class="field__value">
                                <Hash :value="t.hash" truncate="tx" />
                            </div>
                            <div class="field__sub">
                                <span class="method">{{ t.method }}</span>
                            </div>
                        </div>
                        <div class="field">
                            <div class="field__label">From → To</div>
                            <div class="field__value field__value--mono text-xs">
                                <Hash :value="t.from" truncate="address" />
                                <span class="text-[color:var(--color-fg-dim)] mx-1">→</span>
                                <span v-if="t.to_label">{{ t.to_label }}</span>
                                <Hash v-else :value="t.to" truncate="address" />
                            </div>
                        </div>
                        <div class="field text-right">
                            <div class="field__label">Value</div>
                            <div class="field__value field__value--mono">{{ fmtEth(t.value_eth, 3) }}</div>
                        </div>
                        <div class="field text-right">
                            <StatusBadge :status="t.status" />
                        </div>
                    </Link>
                </div>
                <div class="section__foot text-[color:var(--color-fg-muted)]">Updated every 6s</div>
            </div>
        </div>
    </AppLayout>
</template>
