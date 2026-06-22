<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    BadgeCheck,
    Box,
    ArrowRightLeft,
    FileCode2,
    Gauge,
    Network,
    Repeat2,
    Send,
} from 'lucide-vue-next';
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
const liveError = ref(false);
const lastUpdatedAt = ref(new Date());

let timer = null;

const liveLabel = computed(() => (liveError.value ? 'Reconnecting' : 'Live'));
const liveDotClass = computed(() => ({
    'live-dot--warn': liveError.value,
}));

function formatUpdated(date) {
    return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
}

function utilWidth(value) {
    return `${Math.min(100, Math.max(0, Number(value) || 0))}%`;
}

function txTypeMeta(method) {
    switch (String(method).toLowerCase()) {
        case 'transfer':
            return { label: 'Transfer', cls: 'method--transfer', icon: Send };
        case 'swap':
            return { label: 'Swap', cls: 'method--swap', icon: Repeat2 };
        case 'approve':
            return {
                label: 'Approve',
                cls: 'method--approve',
                icon: BadgeCheck,
            };
        default:
            return {
                label: 'Contract call',
                cls: 'method--contract',
                icon: FileCode2,
            };
    }
}

function visitBlock(height) {
    router.visit(`/block/${height}`);
}

function visitTransaction(hash) {
    router.visit(`/tx/${hash}`);
}

async function poll() {
    try {
        const [tipRes, blocksRes, txsRes] = await Promise.all([
            fetch('/api/live/tip', { headers: { Accept: 'application/json' } }),
            fetch('/api/live/blocks', {
                headers: { Accept: 'application/json' },
            }),
            fetch('/api/live/transactions', {
                headers: { Accept: 'application/json' },
            }),
        ]);
        if (!tipRes.ok || !blocksRes.ok || !txsRes.ok) {
            liveError.value = true;
            return;
        }
        const tipData = await tipRes.json();
        const blocksData = await blocksRes.json();
        const txsData = await txsRes.json();

        liveError.value = false;
        lastUpdatedAt.value = new Date();
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
        liveError.value = true;
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
        <section class="hero-search" aria-labelledby="explorer-heading">
            <div class="hero-search__content">
                <div>
                    <div class="hero-search__eyebrow">
                        Ethereum mainnet observability
                    </div>
                    <h1 id="explorer-heading" class="hero-search__title">
                        Explore Ethereum
                        <span>in real time.</span>
                    </h1>
                    <p class="hero-search__copy">
                        Search transactions, addresses, blocks and ENS names
                        across ChainScope's read-only demonstration feed.
                    </p>
                </div>
                <div class="hero-search__tools">
                    <SearchBar hero />
                    <div class="hero-examples" aria-label="Search examples">
                        <span>Try</span>
                        <Link
                            class="hero-example technical-value"
                            :href="`/block/${tip}`"
                            >#{{ fmtInt(tip) }}
                        </Link>
                        <Link class="hero-example" href="/search?q=vitalik.eth">
                            vitalik.eth
                        </Link>
                        <Link
                            v-if="transactions[0]"
                            class="hero-example technical-value"
                            :href="`/tx/${transactions[0].hash}`"
                            >latest tx
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <section class="section pulse-panel" aria-labelledby="pulse-heading">
            <div class="section__head">
                <div class="flex items-center gap-2">
                    <Gauge :size="15" :stroke-width="2" class="text-accent" />
                    <span id="pulse-heading" class="section__title"
                        >Network pulse</span
                    >
                </div>
                <span class="section__sub" aria-live="polite">
                    <span
                        class="live-dot"
                        :class="liveDotClass"
                        aria-hidden="true"
                    />
                    {{ liveLabel }} · updated {{ formatUpdated(lastUpdatedAt) }}
                </span>
            </div>
            <div class="stat-strip">
                <div class="stat-cell">
                    <div class="stat-cell__label">Latest block</div>
                    <div class="stat-cell__value">#{{ fmtInt(tip) }}</div>
                    <div class="stat-cell__meta">
                        <span class="live-dot" aria-hidden="true" />
                        12s average cadence
                    </div>
                </div>
                <div class="stat-cell">
                    <div class="stat-cell__label">Base fee</div>
                    <div class="stat-cell__value">{{ fmtGwei(gas) }}</div>
                    <div class="stat-cell__meta">
                        ≈ {{ fmtUsd((gas * 21000 * ethPrice) / 1e9) }} simple
                        transfer
                    </div>
                </div>
                <div class="stat-cell">
                    <div class="stat-cell__label">ETH price</div>
                    <div class="stat-cell__value">{{ fmtUsd(ethPrice) }}</div>
                    <div class="stat-cell__meta">USD spot reference</div>
                </div>
                <div class="stat-cell">
                    <div class="stat-cell__label">Network</div>
                    <div class="stat-cell__value">Mainnet</div>
                    <div class="stat-cell__meta">
                        <Network :size="13" :stroke-width="2" />
                        Chain ID 1 · PoS
                    </div>
                </div>
            </div>
        </section>

        <div class="split-2">
            <section class="section" aria-labelledby="latest-blocks-heading">
                <div class="section__head">
                    <div class="flex items-center gap-2">
                        <Box
                            :size="15"
                            :stroke-width="2"
                            class="text-[color:var(--color-accent)]"
                        />
                        <span id="latest-blocks-heading" class="section__title"
                            >Latest blocks</span
                        >
                    </div>
                    <span class="section__sub">
                        <span
                            class="live-dot"
                            :class="liveDotClass"
                            aria-hidden="true"
                        />
                        {{ liveLabel }}
                    </span>
                </div>
                <div class="data-list">
                    <div
                        v-for="b in blocks"
                        :key="b.height"
                        class="data-row data-row--linked row-block block-row"
                        :class="{ 'row-flash': flashedBlocks.has(b.height) }"
                        role="link"
                        tabindex="0"
                        @click="visitBlock(b.height)"
                        @keydown.enter.prevent="visitBlock(b.height)"
                        @keydown.space.prevent="visitBlock(b.height)"
                    >
                        <div class="block-row__primary">
                            <div class="block-row__top">
                                <Link
                                    :href="`/block/${b.height}`"
                                    class="block-row__number"
                                    @click.stop
                                    >#{{ fmtInt(b.height) }}</Link
                                >
                                <span class="block-row__age">{{ b.age }}</span>
                            </div>
                            <div class="block-row__summary">
                                <span
                                    >{{ fmtInt(b.tx_count) }} transactions</span
                                >
                                <span class="technical-value"
                                    >{{ b.reward_eth }} ETH</span
                                >
                            </div>
                        </div>
                        <div class="field">
                            <div class="field__label">Validator</div>
                            <div class="field__value">{{ b.validator }}</div>
                        </div>
                        <div class="field">
                            <div class="field__label">Utilization</div>
                            <div class="util-meter">
                                <div
                                    class="util-meter__bar"
                                    role="meter"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                    :aria-valuenow="Number(b.gas_used_pct)"
                                    :aria-label="`${b.gas_used_pct}% gas used`"
                                >
                                    <span
                                        class="util-meter__fill"
                                        :style="{
                                            width: utilWidth(b.gas_used_pct),
                                        }"
                                    />
                                </div>
                                <div class="field__sub technical-value">
                                    {{ b.gas_used_pct }}%
                                </div>
                            </div>
                        </div>
                        <div class="field text-right">
                            <div class="field__label">Reward</div>
                            <div class="field__value field__value--mono">
                                {{ b.reward_eth }} ETH
                            </div>
                        </div>
                    </div>
                    <div v-if="!blocks.length" class="empty-state">
                        No block data is available yet.
                    </div>
                </div>
                <div class="section__foot text-fg-muted">
                    MockChain read-only feed · polling every 6s
                </div>
            </section>

            <section
                class="section"
                aria-labelledby="latest-transactions-heading"
            >
                <div class="section__head">
                    <div class="flex items-center gap-2">
                        <ArrowRightLeft
                            :size="15"
                            :stroke-width="2"
                            class="text-accent"
                        />
                        <span
                            id="latest-transactions-heading"
                            class="section__title"
                            >Latest transactions</span
                        >
                    </div>
                    <span class="section__sub"
                        >{{ transactions.length }} seen</span
                    >
                </div>
                <div class="data-list">
                    <div
                        v-for="t in transactions"
                        :key="t.hash"
                        class="data-row data-row--linked tx-row"
                        :class="{ 'row-flash': flashedTxs.has(t.hash) }"
                        role="link"
                        tabindex="0"
                        @click="visitTransaction(t.hash)"
                        @keydown.enter.prevent="visitTransaction(t.hash)"
                        @keydown.space.prevent="visitTransaction(t.hash)"
                    >
                        <div class="tx-row__identity">
                            <div class="tx-row__top">
                                <Hash
                                    :value="t.hash"
                                    truncate="tx"
                                    copy
                                    label="transaction hash"
                                />
                            </div>
                            <span
                                class="method"
                                :class="txTypeMeta(t.method).cls"
                            >
                                <component
                                    :is="txTypeMeta(t.method).icon"
                                    :size="12"
                                    :stroke-width="2.2"
                                    aria-hidden="true"
                                />
                                {{ txTypeMeta(t.method).label }}
                            </span>
                        </div>
                        <div class="tx-flow">
                            <Hash
                                :value="t.from"
                                truncate="address"
                                copy
                                label="from address"
                            />
                            <span class="tx-flow__arrow" aria-hidden="true"
                                >→</span
                            >
                            <span
                                v-if="t.to_label"
                                class="address-label"
                                :title="t.to"
                                >{{ t.to_label }}</span
                            >
                            <Hash
                                :value="t.to"
                                :truncate="t.to_label ? 'compact' : 'address'"
                                copy
                                label="to address"
                            />
                        </div>
                        <div class="tx-row__amount">
                            {{ fmtEth(t.value_eth, 3) }}
                        </div>
                        <div class="field text-right">
                            <StatusBadge :status="t.status" />
                            <span class="tx-row__age">{{ t.age }}</span>
                        </div>
                    </div>
                    <div v-if="!transactions.length" class="empty-state">
                        No transaction data is available yet.
                    </div>
                </div>
                <div class="section__foot text-fg-muted">
                    Hashes and addresses copy without leaving the live feed.
                </div>
            </section>
        </div>
    </AppLayout>
</template>
