<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRightLeft } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Hash from '@/Components/Hash.vue';
import { fmtInt, fmtEth, fmtUsd, fmtGwei, isoTime } from '@/utils/format.js';

const props = defineProps({
    transaction: Object,
    eth_price: Number,
});
</script>

<template>
    <Head :title="`Tx ${transaction.hash.slice(0, 14)}`" />
    <AppLayout>
        <div class="page-head">
            <div class="min-w-0">
                <div class="page-head__title">
                    <ArrowRightLeft
                        :size="18"
                        :stroke-width="2"
                        class="text-[color:var(--color-accent)]"
                    />
                    Transaction
                    <StatusBadge :status="transaction.status" />
                </div>
                <div class="page-head__sub">{{ transaction.hash }}</div>
            </div>
        </div>

        <div class="section mb-4">
            <div class="section__head">
                <span class="section__title">Overview</span>
                <span class="section__sub">{{
                    isoTime(transaction.timestamp)
                }}</span>
            </div>
            <div class="detail-grid">
                <div class="detail-row">
                    <div class="detail-label">Hash</div>
                    <div class="detail-value mono text-xs break-all">
                        {{ transaction.hash }}
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Status</div>
                    <div class="detail-value">
                        <StatusBadge :status="transaction.status" />
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Block</div>
                    <div class="detail-value">
                        <Link
                            :href="`/block/${transaction.block_height}`"
                            class="hash-link"
                            >#{{ fmtInt(transaction.block_height) }}</Link
                        >
                        <span class="ml-2 text-[color:var(--color-fg-dim)]"
                            >{{
                                fmtInt(transaction.confirmations)
                            }}
                            confirmations</span
                        >
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Timestamp</div>
                    <div class="detail-value">
                        {{ isoTime(transaction.timestamp) }}
                        <span class="text-[color:var(--color-fg-dim)]"
                            >· {{ transaction.age }}</span
                        >
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">From</div>
                    <div class="detail-value">
                        <Link
                            :href="`/address/${transaction.from}`"
                            class="hash-link"
                            >{{ transaction.from }}</Link
                        >
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">To</div>
                    <div class="detail-value">
                        <span v-if="transaction.to_label" class="mr-2">{{
                            transaction.to_label
                        }}</span>
                        <Link
                            :href="`/address/${transaction.to}`"
                            class="hash-link"
                            >{{ transaction.to }}</Link
                        >
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Method</div>
                    <div class="detail-value">
                        <span class="method">{{ transaction.method }}</span>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Value</div>
                    <div class="detail-value mono">
                        {{ fmtEth(transaction.value_eth) }}
                        <span class="ml-2 text-[color:var(--color-fg-dim)]"
                            >≈
                            {{
                                fmtUsd(transaction.value_eth * eth_price)
                            }}</span
                        >
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Transaction fee</div>
                    <div class="detail-value mono">
                        {{ fmtEth(transaction.fee_eth, 6) }}
                        <span class="ml-2 text-[color:var(--color-fg-dim)]"
                            >≈
                            {{ fmtUsd(transaction.fee_eth * eth_price) }}</span
                        >
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Gas price</div>
                    <div class="detail-value mono">
                        {{ fmtGwei(transaction.gas_price_gwei) }}
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Gas limit</div>
                    <div class="detail-value mono">
                        {{ fmtInt(transaction.gas) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section__head">
                <span class="section__title">Transaction context</span>
                <span class="section__sub"
                    >Position {{ transaction.index }} in block</span
                >
            </div>
            <div
                class="section__body section__body--padded text-sm text-[color:var(--color-fg-muted)]"
            >
                This transaction was included in
                <Link
                    :href="`/block/${transaction.block_height}`"
                    class="hash-link"
                    >block #{{ fmtInt(transaction.block_height) }}</Link
                >
                at index {{ transaction.index }}. It has been confirmed by
                {{ fmtInt(transaction.confirmations) }} subsequent block(s).
            </div>
        </div>
    </AppLayout>
</template>
