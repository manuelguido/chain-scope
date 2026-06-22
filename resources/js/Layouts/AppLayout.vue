<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import SearchBar from '@/Components/SearchBar.vue';

defineProps({
    showSearch: { type: Boolean, default: true },
});

const page = usePage();
const explorerActive = computed(
    () =>
        page.url === '/' ||
        ['/search', '/block', '/tx', '/address'].some((path) =>
            page.url.startsWith(path),
        ),
);
</script>

<template>
    <div class="app-shell">
        <header class="app-topbar">
            <div class="app-topbar__inner">
                <Link href="/" class="brand">
                    <span class="brand__mark" aria-hidden="true" />
                    <span class="brand__name"
                        >ChainScope <span>/ Ethereum</span></span
                    >
                </Link>

                <nav class="app-nav" aria-label="Primary navigation">
                    <Link
                        href="/"
                        class="app-nav__link"
                        :class="{ 'app-nav__link--active': explorerActive }"
                        >Explorer</Link
                    >
                </nav>

                <SearchBar v-if="showSearch" />

                <div class="network-status" aria-label="Network status">
                    <span class="network-status__network"
                        >Ethereum Mainnet</span
                    >
                    <span class="network-status__live">
                        <span class="live-dot" aria-hidden="true" />
                        Live status
                    </span>
                </div>
            </div>
        </header>

        <main class="app-content">
            <slot />
        </main>

        <footer class="app-footer">
            <div class="app-footer__inner">
                <span class="app-footer__meta">
                    <span>ChainScope</span>
                    <span class="app-footer__dot" aria-hidden="true" />
                    <span>Read-only explorer</span>
                    <span class="app-footer__dot" aria-hidden="true" />
                    <span>Demonstration data</span>
                </span>
                <span>Ethereum Mainnet · Press <code>/</code> to search</span>
            </div>
        </footer>
    </div>
</template>
