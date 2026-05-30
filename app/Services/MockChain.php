<?php

namespace App\Services;

/**
 * Deterministic mock blockchain data generator.
 * Produces stable, repeatable results across requests so search/lookups work.
 * The "tip" advances with real time so the explorer feels live.
 */
class MockChain
{
    /** Seconds per block (Ethereum mainnet ~12s) */
    private const BLOCK_TIME = 12;

    /** Reference height + timestamp anchor (unix seconds) */
    private const ANCHOR_HEIGHT = 22_104_000;

    private const ANCHOR_TIME = 1_730_000_000;

    public static function tipHeight(): int
    {
        return self::ANCHOR_HEIGHT + intdiv(time() - self::ANCHOR_TIME, self::BLOCK_TIME);
    }

    public static function tipTime(): int
    {
        return self::ANCHOR_TIME + (self::tipHeight() - self::ANCHOR_HEIGHT) * self::BLOCK_TIME;
    }

    public static function gasPriceGwei(): float
    {
        $t = floor(time() / 30);
        $rng = self::seedRng((int) $t, 'gas');

        return round(14 + ($rng / 0xFFFFFFFF) * 28, 2);
    }

    public static function ethPrice(): float
    {
        $t = floor(time() / 60);
        $rng = self::seedRng((int) $t, 'price');

        return round(2400 + ($rng / 0xFFFFFFFF) * 600, 2);
    }

    /** Latest blocks list (newest first) */
    public static function latestBlocks(int $count = 12): array
    {
        $tip = self::tipHeight();
        $out = [];
        for ($i = 0; $i < $count; $i++) {
            $out[] = self::block($tip - $i);
        }

        return $out;
    }

    /** Latest transactions across recent blocks */
    public static function latestTransactions(int $count = 14): array
    {
        $tip = self::tipHeight();
        $out = [];
        $b = 0;
        while (count($out) < $count && $b < 6) {
            $block = self::block($tip - $b);
            foreach ($block['transactions'] as $tx) {
                $out[] = $tx + ['block' => $block['height'], 'age' => $block['age']];
                if (count($out) >= $count) {
                    break 2;
                }
            }
            $b++;
        }

        return $out;
    }

    /** Build block by height */
    public static function block(int $height): array
    {
        $rng = self::seedRng($height, 'block');
        $txCount = 80 + ($rng % 220);
        $gasUsed = 8_000_000 + (($rng >> 8) % 22_000_000);
        $gasLimit = 30_000_000;
        $baseFee = round(self::gasPriceGwei() * (0.85 + (($rng >> 16) % 30) / 100), 2);
        $time = self::ANCHOR_TIME + ($height - self::ANCHOR_HEIGHT) * self::BLOCK_TIME;
        $validator = self::pick(['beaverbuild', 'rsync-builder', 'titan-builder', 'flashbots', 'lido', 'coinbase'], $rng >> 4);
        $rewardEth = round(0.025 + (($rng >> 12) % 80) / 1000, 4);

        return [
            'height' => $height,
            'hash' => self::hash('block-'.$height, 64),
            'parent_hash' => self::hash('block-'.($height - 1), 64),
            'timestamp' => $time,
            'age' => self::ago($time),
            'tx_count' => $txCount,
            'gas_used' => $gasUsed,
            'gas_limit' => $gasLimit,
            'gas_used_pct' => round($gasUsed / $gasLimit * 100, 1),
            'base_fee_gwei' => $baseFee,
            'validator' => $validator,
            'reward_eth' => $rewardEth,
            'size_bytes' => 80_000 + (($rng >> 5) % 80_000),
            'transactions' => self::txsForBlock($height, min($txCount, 12)),
        ];
    }

    /** Build N representative txs for a block */
    public static function txsForBlock(int $height, int $count): array
    {
        $out = [];
        $methods = ['Swap', 'Transfer', 'Mint', 'Bridge', 'Approve', 'Stake', 'Claim', 'Deposit'];
        $contracts = [
            'Uniswap V3' => '0xE592427A0AEce92De3Edee1F18E0157C05861564',
            'Aave V3' => '0x87870Bca3F3fD6335C3F4ce8392D69350B4fA4E2',
            'Lido' => '0xae7ab96520DE3A18E5e111B5EaAb095312D7fE84',
            'Base Portal' => '0x49048044D57e1C92A77f79988d21Fa8fAF74E97e',
            'Zora' => '0x777777C9898D384F785Ee44Acfe945efDFf5f3E0',
        ];
        for ($i = 0; $i < $count; $i++) {
            $rng = self::seedRng($height, 'tx-'.$i);
            $method = $methods[$rng % count($methods)];
            $isContract = ($rng >> 3) % 3 !== 0;
            $contractName = array_rand($contracts);
            $to = $isContract ? $contracts[$contractName] : self::address(($rng >> 7) ^ 0xABCDEF);
            $toLabel = $isContract ? $contractName : null;
            $valueEth = $method === 'Transfer'
                ? round((($rng >> 11) % 5000) / 100, 4)
                : round((($rng >> 11) % 200) / 1000, 4);
            $gas = 21_000 + (($rng >> 9) % 280_000);
            $gasPrice = self::gasPriceGwei() * (0.9 + (($rng >> 17) % 25) / 100);
            $feeEth = ($gas * $gasPrice) / 1e9;
            $statusRoll = $rng % 100;
            $status = $statusRoll < 92 ? 'success' : ($statusRoll < 97 ? 'pending' : 'failed');

            $out[] = [
                'hash' => self::hash('tx-'.$height.'-'.$i, 64),
                'block_height' => $height,
                'index' => $i,
                'from' => self::address(($rng >> 5) ^ 0x123456),
                'to' => $to,
                'to_label' => $toLabel,
                'method' => $method,
                'value_eth' => $valueEth,
                'gas' => $gas,
                'gas_price_gwei' => round($gasPrice, 3),
                'fee_eth' => round($feeEth, 6),
                'status' => $status,
                'timestamp' => self::ANCHOR_TIME + ($height - self::ANCHOR_HEIGHT) * self::BLOCK_TIME,
            ];
        }

        return $out;
    }

    public static function findTransaction(string $hash): ?array
    {
        // Search recent blocks for the hash
        $tip = self::tipHeight();
        for ($i = 0; $i < 200; $i++) {
            $block = self::block($tip - $i);
            foreach ($block['transactions'] as $tx) {
                if (strcasecmp($tx['hash'], $hash) === 0) {
                    return $tx + [
                        'age' => $block['age'],
                        'confirmations' => $i,
                        'block_hash' => $block['hash'],
                    ];
                }
            }
        }
        // Fallback: synthesize a tx from the hash so any user-provided hash resolves to a page
        $rng = self::seedRng(crc32($hash), 'lookup');
        $height = $tip - ($rng % 50);
        $tx = self::txsForBlock($height, 1)[0];
        $tx['hash'] = self::normalizeHash($hash, 64);
        $tx['block_height'] = $height;
        $tx['age'] = self::ago(self::ANCHOR_TIME + ($height - self::ANCHOR_HEIGHT) * self::BLOCK_TIME);
        $tx['confirmations'] = $tip - $height;
        $tx['block_hash'] = self::hash('block-'.$height, 64);

        return $tx;
    }

    public static function addressInfo(string $address): array
    {
        $addr = self::normalizeHash($address, 40);
        $rng = self::seedRng(crc32(strtolower($addr)), 'addr');
        $isContract = ($rng % 5) === 0;
        $balanceEth = round(($rng % 500_000) / 100, 4);
        $txTotal = 200 + (($rng >> 4) % 50_000);
        $firstSeen = time() - 86_400 * (30 + (($rng >> 8) % 1500));
        $labels = [
            '0xd8da6bf26964af9d7eed9e03e53415d37aa96045' => 'vitalik.eth',
            '0x28c6c06298d514db089934071355e5743bf21d60' => 'Binance: Hot Wallet',
            '0x742d35cc6634c0532925a3b844bc9e7595f0beb7' => 'Whale (top 50)',
        ];
        $label = $labels[strtolower($addr)] ?? null;

        return [
            'address' => $addr,
            'label' => $label,
            'is_contract' => $isContract,
            'balance_eth' => $balanceEth,
            'tx_count' => $txTotal,
            'first_seen' => $firstSeen,
            'first_seen_age' => self::ago($firstSeen),
            'transactions' => self::txsForAddress($addr, 20),
            'tokens' => self::tokensForAddress($addr),
        ];
    }

    private static function txsForAddress(string $addr, int $count): array
    {
        $tip = self::tipHeight();
        $out = [];
        for ($i = 0; $i < $count; $i++) {
            $rng = self::seedRng(crc32(strtolower($addr)) ^ $i, 'addr-tx');
            $blockOffset = $i * 3 + ($rng % 5);
            $height = $tip - $blockOffset;
            $time = self::ANCHOR_TIME + ($height - self::ANCHOR_HEIGHT) * self::BLOCK_TIME;
            $methods = ['Swap', 'Transfer', 'Approve', 'Stake', 'Claim'];
            $method = $methods[$rng % count($methods)];
            $direction = ($rng >> 3) % 2 === 0 ? 'out' : 'in';
            $other = self::address(($rng >> 5) ^ 0xFEDCBA);
            $valueEth = round((($rng >> 7) % 8000) / 1000, 4);

            $out[] = [
                'hash' => self::hash('addr-tx-'.strtolower($addr).'-'.$i, 64),
                'block_height' => $height,
                'timestamp' => $time,
                'age' => self::ago($time),
                'method' => $method,
                'direction' => $direction,
                'from' => $direction === 'out' ? $addr : $other,
                'to' => $direction === 'out' ? $other : $addr,
                'value_eth' => $valueEth,
                'fee_eth' => round((21000 + ($rng >> 9) % 200000) * self::gasPriceGwei() / 1e9, 6),
                'status' => ($rng % 100) < 95 ? 'success' : 'failed',
            ];
        }

        return $out;
    }

    private static function tokensForAddress(string $addr): array
    {
        $rng = self::seedRng(crc32(strtolower($addr)), 'tokens');
        $catalog = [
            ['symbol' => 'USDC', 'name' => 'USD Coin', 'decimals' => 6],
            ['symbol' => 'USDT', 'name' => 'Tether USD', 'decimals' => 6],
            ['symbol' => 'WETH', 'name' => 'Wrapped Ether', 'decimals' => 18],
            ['symbol' => 'DAI', 'name' => 'Dai Stablecoin', 'decimals' => 18],
            ['symbol' => 'LINK', 'name' => 'Chainlink', 'decimals' => 18],
            ['symbol' => 'UNI', 'name' => 'Uniswap', 'decimals' => 18],
        ];
        $count = 2 + ($rng % 4);
        $out = [];
        for ($i = 0; $i < $count; $i++) {
            $tok = $catalog[($rng + $i * 7) % count($catalog)];
            $r2 = self::seedRng(crc32(strtolower($addr)) ^ ($i * 31), 'tok-bal');
            $balance = round(($r2 % 5_000_000) / 100, 2);
            $usd = $balance * (1 + ($r2 % 30) / 10);
            $out[] = $tok + ['balance' => $balance, 'usd_value' => round($usd, 2)];
        }

        return $out;
    }

    /** Detect input type for global search */
    public static function detect(string $input): array
    {
        $q = trim($input);
        if ($q === '') {
            return ['type' => 'empty', 'value' => ''];
        }
        // Block number
        if (preg_match('/^\d+$/', $q)) {
            return ['type' => 'block', 'value' => (int) $q];
        }
        // 0x-prefixed
        if (preg_match('/^0x[0-9a-fA-F]+$/', $q)) {
            $len = strlen($q) - 2;
            if ($len === 64) {
                return ['type' => 'tx', 'value' => $q];
            }
            if ($len === 40) {
                return ['type' => 'address', 'value' => $q];
            }
        }
        // ENS-like
        if (str_ends_with($q, '.eth')) {
            $resolved = self::resolveEns($q);

            return ['type' => 'address', 'value' => $resolved, 'ens' => $q];
        }

        return ['type' => 'unknown', 'value' => $q];
    }

    private static function resolveEns(string $name): string
    {
        $known = [
            'vitalik.eth' => '0xd8da6bf26964af9d7eed9e03e53415d37aa96045',
        ];

        return $known[strtolower($name)] ?? self::address(crc32(strtolower($name)));
    }

    // ─────────────────────────────────────────────────────────────────────
    // Helpers

    private static function seedRng(int $seed, string $salt): int
    {
        $h = crc32($salt.':'.$seed);
        // mix
        $h ^= ($h << 13) & 0xFFFFFFFF;
        $h ^= ($h >> 17);
        $h ^= ($h << 5) & 0xFFFFFFFF;

        return $h & 0xFFFFFFFF;
    }

    private static function pick(array $arr, int $n): string
    {
        return $arr[abs($n) % count($arr)];
    }

    public static function hash(string $seed, int $len = 64): string
    {
        $hex = hash('sha256', $seed);

        return '0x'.substr($hex, 0, $len);
    }

    public static function address(int $seed): string
    {
        return self::hash('addr-'.$seed, 40);
    }

    public static function normalizeHash(string $input, int $len): string
    {
        $clean = strtolower(preg_replace('/[^0-9a-fx]/i', '', $input));
        if (! str_starts_with($clean, '0x')) {
            $clean = '0x'.$clean;
        }
        $hex = substr($clean, 2);
        if (strlen($hex) > $len) {
            $hex = substr($hex, 0, $len);
        }
        if (strlen($hex) < $len) {
            $hex = str_pad($hex, $len, '0', STR_PAD_RIGHT);
        }

        return '0x'.$hex;
    }

    public static function ago(int $ts): string
    {
        $d = max(0, time() - $ts);
        if ($d < 60) {
            return $d.'s ago';
        }
        if ($d < 3600) {
            return intdiv($d, 60).'m ago';
        }
        if ($d < 86400) {
            return intdiv($d, 3600).'h ago';
        }

        return intdiv($d, 86400).'d ago';
    }
}
