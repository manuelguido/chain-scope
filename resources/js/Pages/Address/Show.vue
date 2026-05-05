<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Wallet, FileCode2, ArrowDownRight, ArrowUpRight } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Hash from '@/Components/Hash.vue';
import { fmtInt, fmtEth, fmtUsd, isoTime } from '@/utils/format.js';

const props = defineProps({
    data: Object,
    eth_price: Number,
});

const tab = ref('transactions');
</script>

<template>
    <Head :title="`Address ${data.address.slice(0, 10)}`" />
    <AppLayout>
        <div class="page-head">
            <div class="min-w-0">
                <div class="page-head__title">
                    <component
                        :is="data.is_contract ? FileCode2 : Wallet"
                        :size="18"
                        :stroke-width="2"
                        class="text-[color:var(--color-accent)]"
                    />
                    {{ data.is_contract ? 'Contract' : 'Address' }}
                    <span v-if="data.label" class="pill pill--accent">{{ data.label }}</span>
                </div>
                <div class="page-head__sub">{{ data.address }}</div>
            </div>
        </div>

        <!-- Top stats -->
        <div class="stat-strip mb-4">
            <div class="stat-cell">
                <div class="stat-cell__label">Balance</div>
                <div class="stat-cell__value">{{ fmtEth(data.balance_eth) }}</div>
                <div class="stat-cell__meta">{{ fmtUsd(data.balance_eth * eth_price) }}</div>
            </div>
            <div class="stat-cell">
                <div class="stat-cell__label">Transactions</div>
                <div class="stat-cell__value">{{ fmtInt(data.tx_count) }}</div>
                <div class="stat-cell__meta">total</div>
            </div>
            <div class="stat-cell">
                <div class="stat-cell__label">First seen</div>
                <div class="stat-cell__value text-base">{{ data.first_seen_age }}</div>
                <div class="stat-cell__meta">{{ isoTime(data.first_seen) }}</div>
            </div>
            <div class="stat-cell">
                <div class="stat-cell__label">Type</div>
                <div class="stat-cell__value">{{ data.is_contract ? 'Contract' : 'EOA' }}</div>
                <div class="stat-cell__meta">{{ data.is_contract ? 'Smart contract' : 'Externally owned' }}</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="section">
            <div class="tabs">
                <button class="tab" :class="{ 'tab--active': tab === 'transactions' }" @click="tab = 'transactions'">
                    Transactions ({{ data.transactions.length }})
                </button>
                <button class="tab" :class="{ 'tab--active': tab === 'tokens' }" @click="tab = 'tokens'">
                    Token holdings ({{ data.tokens.length }})
                </button>
            </div>

            <!-- Transactions tab -->
            <div v-if="tab === 'transactions'" class="data-list">
                <Link
                    v-for="t in data.transactions"
                    :key="t.hash"
                    :href="`/tx/${t.hash}`"
                    class="data-row row-tx"
                >
                    <div class="row-icon">
                        <component
                            :is="t.direction === 'in' ? ArrowDownRight : ArrowUpRight"
                            :size="14"
                            :stroke-width="2"
                            :class="t.direction === 'in' ? 'text-[color:var(--color-ok)]' : 'text-[color:var(--color-fg-muted)]'"
                        />
                    </div>
                    <div class="field">
                        <div class="field__value"><Hash :value="t.hash" truncate="tx" /></div>
                        <div class="field__sub">
                            <span class="method">{{ t.method }}</span>
                            <span class="ml-1 text-[color:var(--color-fg-dim)]">{{ t.age }}</span>
                        </div>
                    </div>
                    <div class="field">
                        <div class="field__label">{{ t.direction === 'in' ? 'From' : 'To' }}</div>
                        <div class="field__value field__value--mono text-xs">
                            <Hash :value="t.direction === 'in' ? t.from : t.to" truncate="address" />
                        </div>
                    </div>
                    <div class="field text-right">
                        <div class="field__label">Value</div>
                        <div
                            class="field__value field__value--mono"
                            :class="t.direction === 'in' ? 'text-[color:var(--color-ok)]' : ''"
                        >
                            {{ t.direction === 'in' ? '+' : '-' }}{{ fmtEth(t.value_eth, 3) }}
                        </div>
                    </div>
                    <div class="field text-right">
                        <StatusBadge :status="t.status" />
                    </div>
                </Link>
            </div>

            <!-- Tokens tab -->
            <div v-else class="data-list">
                <div
                    v-for="tok in data.tokens"
                    :key="tok.symbol"
                    class="data-row"
                    style="grid-template-columns: 36px minmax(0,1fr) auto auto;"
                >
                    <div class="row-icon mono text-[10px]">{{ tok.symbol.slice(0, 3) }}</div>
                    <div class="field">
                        <div class="field__value">{{ tok.name }}</div>
                        <div class="field__sub mono">{{ tok.symbol }}</div>
                    </div>
                    <div class="field text-right">
                        <div class="field__label">Balance</div>
                        <div class="field__value field__value--mono">{{ fmtInt(tok.balance) }} {{ tok.symbol }}</div>
                    </div>
                    <div class="field text-right">
                        <div class="field__label">Value</div>
                        <div class="field__value field__value--mono">{{ fmtUsd(tok.usd_value) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
