# ChainScope

ChainScope is a self-contained blockchain explorer for reviewing the product shape of an Ethereum-style block explorer. It has a live-feeling home page, global search, block pages, transaction pages, address pages, copyable hashes, and deeply linked detail views.

The repository is designed for engineers who want to study explorer interaction patterns without setting up an RPC provider, an indexer, credentials, or background ingestion jobs.

## Explorer Experience

Blockchain explorers are dense products. A useful explorer needs to make hashes, heights, accounts, fees, confirmations, and network activity navigable without burying the user in raw data.

ChainScope isolates that surface in a local application. The data is illustrative, but the navigation model, polling behavior, formatting, and request flow are real application code.

## Features

- Explorer home page with network pulse cards.
- Live polling every 6 seconds for tip height, gas price, ETH price reference, latest blocks, and latest transactions.
- Search for block numbers, transaction hashes, addresses, and ENS-like names.
- Block detail pages with hash, parent hash, validator, gas usage, base fee, reward, size, confirmations, and representative transactions.
- Transaction detail pages with status, block context, sender, recipient, method, value, fee, gas price, and confirmations.
- Address detail pages with balance, transaction count, first-seen time, account type, recent activity, and token holdings.
- Copyable hashes and addresses.
- Deterministic mock data so searches and detail pages are repeatable.
- Graceful not-found page for unknown search input.

## Data Boundary

ChainScope does not read Ethereum mainnet. All blocks, transactions, addresses, balances, token holdings, and metrics come from `App\Services\MockChain`.

The mock service has two deliberate behaviors:

- It advances the chain tip based on wall-clock time using a 12-second block cadence.
- It generates deterministic data from seeds, heights, hashes, and addresses so a route remains stable across requests.

That means the explorer can feel live while remaining fully local. It also means balances, prices, transactions, validators, labels, and token holdings are not real network data.

## Routes

| Route | Purpose |
| --- | --- |
| `/` | Explorer home page |
| `/search?q=...` | Detects the query type and redirects to the matching resource |
| `/tx/{hash}` | Transaction detail |
| `/block/{height}` | Block detail |
| `/address/{address}` | Address detail |
| `/api/live/tip` | JSON endpoint for tip, gas, ETH price, and timestamp |
| `/api/live/blocks` | JSON endpoint for latest blocks |
| `/api/live/transactions` | JSON endpoint for latest transactions |

## Search Behavior

The search controller delegates detection to `MockChain::detect()`:

- numeric input becomes a block lookup
- `0x` plus 64 hex characters becomes a transaction lookup
- `0x` plus 40 hex characters becomes an address lookup
- names ending in `.eth` resolve through the mock ENS resolver
- blank search redirects home
- anything else renders the not-found page

`vitalik.eth` is mapped to a known mock address. Other `.eth` names resolve deterministically from their name.

## Project Layout

```text
app/Services/MockChain.php
    Deterministic chain data generator and search classifier.

app/Http/Controllers/
    Explorer, search, block, transaction, address, and live JSON endpoints.

resources/js/Pages/Explorer/Index.vue
    Home screen with search, network pulse, latest blocks, and latest txs.

resources/js/Pages/Block/Show.vue
resources/js/Pages/Transaction/Show.vue
resources/js/Pages/Address/Show.vue
    Detail screens for explorer resources.

resources/js/Components/
    Shared layout, search bar, status badge, and hash display components.

resources/js/utils/format.js
    Formatting helpers for ETH, USD, gwei, byte size, integer, and timestamps.
```

## Stack

| Layer | Tools |
| --- | --- |
| Backend | Laravel 13, PHP 8.3+, Inertia Laravel |
| Frontend | Vue 3, Vite, Tailwind CSS 4 |
| UI | lucide-vue-next |
| Data | Deterministic mock blockchain service |
| Quality | PHPUnit, Laravel Pint, ESLint, Prettier |

## Local Setup

Install dependencies:

```bash
composer install
npm ci
```

Create and configure the local environment:

```bash
cp .env.example .env
php artisan key:generate
```

Configure the database connection in `.env` before running migrations.

```bash
php artisan migrate
```

Run the local development stack:

```bash
composer dev
```

Or run the server and Vite separately:

```bash
php artisan serve
npm run dev
```

## Build

```bash
npm run build
```

## Tests And Quality

Run the backend test suite:

```bash
composer test
```

Format PHP:

```bash
composer lint
```

Run frontend checks:

```bash
npm run lint:check
npm run format:check
```

Apply frontend fixes:

```bash
npm run lint:fix
npm run format
```

## Implementation Notes

The explorer home page receives initial data from `ExplorerController` and then refreshes with the three `/api/live/*` endpoints. New blocks and transactions are detected in the client by comparing incoming ids against the previous list, then temporarily highlighted.

Transaction lookups search recent mock blocks first. If the hash is syntactically valid but not in the recent window, `MockChain` synthesizes a transaction page from the hash so the route still produces a useful detail view.

Address pages synthesize account metadata, recent transactions, and token holdings from the normalized address. A small set of known labels is included for recognizable examples.

## Current Limits

- No RPC connection.
- No real balances, logs, receipts, traces, contracts, or token transfers.
- No indexing pipeline or queued ingestion.
- No persistence for chain data.
- No dedicated tests yet for `MockChain`, search redirects, or explorer detail routes.

## Contributing

Useful improvements should preserve the no-external-service development experience unless the repository deliberately changes scope.

Good next areas:

- tests for `MockChain::detect()` and deterministic generators
- feature tests for search redirects and detail pages
- clearer empty/error states for live polling
- richer mock receipts, logs, and contract calls
- an adapter boundary for a future real chain provider

Before opening a pull request, run:

```bash
composer test
npm run lint:check
npm run format:check
npm run build
```

## License

ChainScope is open-sourced under the MIT license. See `LICENSE`.
