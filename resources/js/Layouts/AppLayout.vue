<script setup>
import { Link } from '@inertiajs/vue3';
import { Hexagon, Github } from 'lucide-vue-next';
import SearchBar from '@/Components/SearchBar.vue';

defineProps({
    showSearch: { type: Boolean, default: true },
});
</script>

<template>
    <div class="app-shell">
        <header class="app-topbar">
            <div class="app-topbar__inner">
                <Link href="/" class="brand">
                    <span class="brand__mark"><Hexagon :size="16" :stroke-width="2" /></span>
                    <span class="brand__name">Chain Scope <span>/ Ethereum</span></span>
                </Link>

                <SearchBar v-if="showSearch" />

                <div class="hidden md:flex items-center gap-2">
                    <span class="pill"><span class="live-dot" /> Mainnet</span>
                </div>
            </div>
        </header>

        <main class="app-content">
            <slot />
        </main>

        <footer class="app-footer">
            <div class="app-footer__inner">
                <span>Chain Scope · Read-only blockchain explorer · Demonstration data</span>
                <span>Press <code>/</code> to search</span>
            </div>
        </footer>
    </div>
</template>
