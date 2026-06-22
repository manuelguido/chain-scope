<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Box, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Hash from '@/Components/Hash.vue';
import { fmtInt, fmtEth, fmtBytes, fmtGwei, isoTime } from '@/utils/format.js';

const props = defineProps({
    block: Object,
    tip: Number,
    confirmations: Number,
    eth_price: Number,
});
</script>

<template>
    <Head :title="`Block #${block.height}`" />
    <AppLayout>
        <div class="page-head">
            <div class="min-w-0">
                <div class="page-head__title">
                    <Box
                        :size="18"
                        :stroke-width="2"
                        class="text-[color:var(--color-accent)]"
                    />
                    Block <span class="num">#{{ fmtInt(block.height) }}</span>
                    <span class="pill pill--ok"
                        >{{ fmtInt(confirmations) }} confirmations</span
                    >
                </div>
                <div class="page-head__sub">
                    <Hash
                        :value="block.hash"
                        truncate="full"
                        copy
                        label="block hash"
                    />
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Link :href="`/block/${block.height - 1}`" class="btn-ghost"
                    ><ChevronLeft :size="14" /> Prev</Link
                >
                <Link
                    v-if="block.height < tip"
                    :href="`/block/${block.height + 1}`"
                    class="btn-ghost"
                    >Next <ChevronRight :size="14"
                /></Link>
            </div>
        </div>

        <div class="section mb-4">
            <div class="section__head">
                <span class="section__title">Overview</span>
                <span class="section__sub">{{ isoTime(block.timestamp) }}</span>
            </div>
            <div class="detail-grid">
                <div class="detail-row">
                    <div class="detail-label">Block height</div>
                    <div class="detail-value mono">
                        {{ fmtInt(block.height) }}
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Timestamp</div>
                    <div class="detail-value">
                        {{ isoTime(block.timestamp) }}
                        <span class="text-[color:var(--color-fg-dim)]"
                            >· {{ block.age }}</span
                        >
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Hash</div>
                    <div class="detail-value mono text-xs break-all">
                        <Hash
                            :value="block.hash"
                            truncate="full"
                            copy
                            label="block hash"
                        />
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Parent hash</div>
                    <div class="detail-value">
                        <Link
                            :href="`/block/${block.height - 1}`"
                            class="hash-link"
                        >
                            <Hash :value="block.parent_hash" truncate="block" />
                        </Link>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Validator</div>
                    <div class="detail-value">{{ block.validator }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Transactions</div>
                    <div class="detail-value mono">
                        {{ fmtInt(block.tx_count) }}
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Gas used</div>
                    <div class="detail-value mono">
                        {{ fmtInt(block.gas_used) }}
                        <span class="text-[color:var(--color-fg-dim)]"
                            >/ {{ fmtInt(block.gas_limit) }} ({{
                                block.gas_used_pct
                            }}%)</span
                        >
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Base fee</div>
                    <div class="detail-value mono">
                        {{ fmtGwei(block.base_fee_gwei) }}
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Block reward</div>
                    <div class="detail-value mono">
                        {{ fmtEth(block.reward_eth) }}
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Size</div>
                    <div class="detail-value mono">
                        {{ fmtBytes(block.size_bytes) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section__head">
                <span class="section__title">Transactions in this block</span>
                <span class="section__sub"
                    >{{ block.transactions.length }} of
                    {{ fmtInt(block.tx_count) }}</span
                >
            </div>
            <div class="data-list">
                <Link
                    v-for="t in block.transactions"
                    :key="t.hash"
                    :href="`/tx/${t.hash}`"
                    class="data-row row-tx"
                >
                    <div class="row-icon mono text-xs">{{ t.index }}</div>
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
                            <span class="mx-1 text-[color:var(--color-fg-dim)]"
                                >→</span
                            >
                            <span v-if="t.to_label">{{ t.to_label }}</span>
                            <Hash v-else :value="t.to" truncate="address" />
                        </div>
                    </div>
                    <div class="field text-right">
                        <div class="field__label">Value</div>
                        <div class="field__value field__value--mono">
                            {{ fmtEth(t.value_eth, 3) }}
                        </div>
                    </div>
                    <div class="field text-right">
                        <StatusBadge :status="t.status" />
                    </div>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
